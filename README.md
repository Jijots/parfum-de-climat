# Parfum de Climat

A portfolio-grade lifestyle app that reads your GPS-based local weather in real time and recommends the three fragrances from your personal collection that are best suited to today's temperature, humidity, and season.

---

## Architecture

```
Parfum de Climat/
├── backend/    Laravel 11  — REST API + Blade admin panel
├── engine/     Python 3.11 — Climate scoring recommendation engine
└── mobile/     Flutter     — iOS + Android mobile app
```

**Data flow:**
Flutter → `POST /api/v1/recommend` → Laravel → Python engine (subprocess or FastAPI) → scored fragrances → Flutter

---

## Prerequisites

| Tool | Version | Required by |
|---|---|---|
| PHP | ≥ 8.3 | backend |
| Composer | ≥ 2.x | backend |
| Node.js | ≥ 20.x | backend (Vite/Tailwind) |
| MySQL | ≥ 8.0 (via XAMPP) | backend |
| Python | ≥ 3.11 | engine |
| pip | latest | engine |
| Flutter SDK | ≥ 3.22 | mobile |
| Dart | ≥ 3.4 | mobile (included with Flutter) |

---

## 1 · Backend Setup (Laravel)

### 1.1 Install PHP dependencies

```bash
cd backend
composer install
```

### 1.2 Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Then edit `.env`:

```env
# Database — match your XAMPP MySQL credentials
DB_DATABASE=parfum_de_climat
DB_USERNAME=root
DB_PASSWORD=           # blank by default in XAMPP

# Required for weather recommendations
OPENWEATHERMAP_API_KEY=your_api_key_here

# Optional — only if you move the engine/ folder
# ENGINE_SCRIPT_PATH=C:/xampp/htdocs/Parfum de Climat/engine/recommendation_engine.py
```

Get a free OpenWeatherMap key at <https://openweathermap.org/api>.

### 1.3 Create the database

Open **phpMyAdmin** (http://localhost/phpmyadmin) or run:

```sql
CREATE DATABASE parfum_de_climat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 1.4 Run migrations and seed note profiles

```bash
php artisan migrate
php artisan db:seed --class=NoteClimateProfileSeeder
```

### 1.5 Create the storage symlink

```bash
php artisan storage:link
```

This makes uploaded fragrance images accessible at `/storage/fragrances/…`.

### 1.6 Install Node dependencies and build assets

```bash
npm install
npm run build        # production build
# or:
npm run dev          # hot-reload dev server (leave running in a terminal)
```

### 1.7 Create the first admin user

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('your-password'),
    'role'     => 'admin',
]);
```

### 1.8 Access the panel

| URL | Description |
|---|---|
| `http://localhost/Parfum de Climat/backend/public/` | Landing page |
| `http://localhost/Parfum de Climat/backend/public/admin` | Admin panel login |
| `http://localhost/Parfum de Climat/backend/public/api/v1/` | REST API (Sanctum auth) |

> **Tip:** Set up a virtual host in XAMPP (edit `httpd-vhosts.conf`) pointing to
> `c:/xampp/htdocs/Parfum de Climat/backend/public` for a cleaner URL like `http://parfumdeclimat.test`.

---

## 2 · Python Engine Setup

The engine runs as a subprocess called by Laravel per-request (default mode). It can also run as a standalone FastAPI microservice.

### 2.1 Create a virtual environment

```bash
cd engine
python -m venv venv
```

### 2.2 Activate and install dependencies

**Windows (PowerShell or Git Bash):**
```bash
source venv/Scripts/activate    # Git Bash
# or:
venv\Scripts\activate           # PowerShell / CMD
```

**macOS / Linux:**
```bash
source venv/bin/activate
```

```bash
pip install -r requirements.txt
```

### 2.3 Test the engine directly

```bash
python recommendation_engine.py
```

This runs the engine in test mode and prints a sample recommendation.

### 2.4 Microservice mode (optional)

If you set `ENGINE_MODE=microservice` in the Laravel `.env`:

```bash
python recommendation_engine.py --serve
# Starts FastAPI on http://127.0.0.1:8001
```

> **Note:** In subprocess mode (default), `PYTHON_EXECUTABLE` in `.env` must resolve
> correctly. On XAMPP for Windows, `python` usually works if Python is in your PATH.
> If XAMPP's Apache can't find `python`, set the full path:
> `PYTHON_EXECUTABLE=C:/Users/YourName/AppData/Local/Programs/Python/Python311/python.exe`

---

## 3 · Flutter Mobile Setup

### 3.1 Install dependencies

```bash
cd mobile
flutter pub get
```

### 3.2 Configure the API base URL

Edit `lib/core/api/api_endpoints.dart` and set `baseUrl` to match your Laravel backend:

```dart
// Development (XAMPP Apache)
static const String baseUrl = 'http://10.0.2.2/Parfum de Climat/backend/public/api/v1';
// Note: Android emulator uses 10.0.2.2 to reach localhost on the host machine.
// Use your machine's LAN IP for a physical device:
// static const String baseUrl = 'http://192.168.1.x/Parfum de Climat/backend/public/api/v1';
```

### 3.3 Regenerate code (if models change)

```bash
# On Windows with XAMPP — PowerShell must be in PATH
export PATH="/c/Windows/System32/WindowsPowerShell/v1.0:$PATH"
dart run build_runner build --delete-conflicting-outputs
```

### 3.4 Run on an emulator or device

```bash
flutter run
```

---

## Development Workflow

For active development, run these concurrently in separate terminals:

| Terminal | Command | Purpose |
|---|---|---|
| 1 | XAMPP Apache + MySQL | Serves Laravel via Apache |
| 2 | `cd backend && npm run dev` | Vite hot-reload for CSS/JS |
| 3 | `cd engine && python recommendation_engine.py --serve` | Optional: FastAPI microservice |
| 4 | `cd mobile && flutter run` | Flutter app on emulator |

---

## Seeding Fragrance Data

The project includes an Artisan command to import fragrances from the PerfumAPI service (Fragrantica data):

```bash
php artisan fragrances:import --limit=100
```

You can also add fragrances manually through the admin panel at `/admin/fragrances/create`.

After adding fragrances, map their notes to climate profiles in the admin panel:
`/admin/note-profiles` — the **Unmapped Notes** alert on the dashboard will guide you.

---

## Deploying to Railway

The backend (Laravel + Python engine) can be deployed to [Railway](https://railway.app). The mobile app points to the Railway URL as its `baseUrl`.

### Step 1 — Create a Railway project

1. Push the repo to GitHub.
2. In Railway, click **New Project → Deploy from GitHub repo** and select the repo.
3. Set the **Root Directory** of the service to `backend`.

### Step 2 — Add a MySQL database

In your Railway project, click **Add Service → Database → MySQL**. Railway will inject `MYSQL_URL` automatically.

### Step 3 — Set environment variables

In the Railway service's **Variables** tab, add:

| Variable | Value |
|---|---|
| `APP_KEY` | Run `php artisan key:generate --show` locally and paste the result |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | From Railway MySQL service (Variables tab) |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | From Railway MySQL service |
| `DB_USERNAME` | From Railway MySQL service |
| `DB_PASSWORD` | From Railway MySQL service |
| `OPENWEATHERMAP_API_KEY` | Your key from openweathermap.org |
| `ENGINE_MODE` | `subprocess` |
| `PYTHON_EXECUTABLE` | `python3` |
| `ENGINE_SCRIPT_PATH` | `/app/engine/recommendation_engine.py` |

> **Storage:** Railway's filesystem is ephemeral — uploaded fragrance images won't survive redeploys. For production, configure `FILESYSTEM_DISK=s3` and use an S3-compatible bucket (Railway supports this via environment variables).

### Step 4 — Update the Flutter base URL

After Railway gives you a domain (e.g. `https://your-app.railway.app`), update `lib/core/api/api_endpoints.dart`:

```dart
static const String baseUrl = 'https://your-app.railway.app/api/v1';
```

---

## Project Status

| Layer | Status |
|---|---|
| Backend (Laravel API + Blade admin) | ✅ Complete |
| Python recommendation engine | ✅ Complete |
| Flutter app foundation (navigation, state, models) | ✅ Complete |
| Flutter UI screens | ✅ Complete |
