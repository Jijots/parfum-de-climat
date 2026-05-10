@extends('layouts.app')

@section('title', 'Recommendations')

@section('content')
<div
    class="mx-auto max-w-6xl px-6 py-16"
    x-data="{
        status: 'idle',
        weather: null,
        recommendations: [],
        pickUps: [],
        logId: null,
        chosenId: null,
        error: null,
        city: '',
        isGuest: {{ auth()->check() ? 'false' : 'true' }},

        async useLocation() {
            if (window.location.protocol !== 'https:') {
                this.error = 'Location requires a secure connection (HTTPS). Please enter a city name instead.';
                return;
            }
            if (!navigator.geolocation) {
                this.error = 'Geolocation is not supported by your browser. Please enter a city instead.';
                return;
            }
            this.status = 'loading';
            this.error = null;
            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    await this.fetchWeather({ latitude: pos.coords.latitude, longitude: pos.coords.longitude });
                    if (this.weather?.location) this.city = this.weather.location;
                },
                (err) => {
                    this.status = 'idle';
                    this.error = err.code === 2
                        ? 'Your location could not be determined. Please enter a city name instead.'
                        : 'Location access was denied. Please enter a city name instead.';
                }
            );
        },

        async useCity() {
            if (!this.city.trim()) return;
            this.status = 'loading';
            this.error = null;
            await this.fetchWeather({ city: this.city.trim() });
        },

        async fetchWeather(payload) {
            try {
                const res = await window.fetch('{{ route('app.recommend') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    this.error = data.message || 'Something went wrong. Please try again.';
                    this.status = 'idle';
                    return;
                }
                const data = await res.json();
                this.weather  = data.weather;
                this.recommendations = data.recommendations;
                this.pickUps  = data.pick_ups || [];
                this.logId    = data.log_id;
                this.isGuest  = data.guest ?? this.isGuest;
                this.status   = 'done';
            } catch (e) {
                this.error  = 'Could not reach the server. Please try again.';
                this.status = 'idle';
            }
        },

        async choose(fragranceId) {
            if (this.chosenId || !this.logId) return;
            this.chosenId = fragranceId;
            await window.fetch('{{ url('/app/recommend') }}/' + this.logId + '/choose', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ fragrance_id: fragranceId }),
            });
        },

        seasonEmoji(season) {
            const map = { spring:'🌸', summer:'☀️', autumn:'🍂', winter:'❄️' };
            return map[(season||'').toLowerCase()] || '🌡️';
        }
    }"
>

    {{-- Heading --}}
    <div class="mb-8">
        <h1 class="font-display text-[var(--ink)]" style="font-size: 2.5rem; font-weight: 300;">Today's Recommendation</h1>
        <p class="text-[var(--muted)]" style="margin-top: 0.5rem; font-size: 0.875rem;"
           x-text="isGuest
               ? 'Use your temporary wardrobe for session-only recommendations, or sign in to save everything to your account.'
               : 'We\'ll match your wardrobe to the current weather.'">
        </p>
    </div>

    {{-- Guest sign-in nudge (only shown before results load, at top) --}}
    @guest
    <div class="glass" style="border-radius: 0.75rem; padding: 0.875rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; border: 1px solid var(--hairline);">
        <div style="display: flex; align-items: center; gap: 0.625rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width: 16px; height: 16px; color: var(--color-accent); flex-shrink: 0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <p class="text-[var(--ink)]" style="font-size: 0.8rem;">You're browsing as a guest. Your wardrobe and recommendations last for this session only unless you sign in.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
            <a href="{{ route('register') }}" class="btn-primary" style="font-size: 0.75rem; padding: 0.35rem 0.875rem;">Create account</a>
            <a href="{{ route('login') }}" class="btn-ghost" style="font-size: 0.75rem; padding: 0.35rem 0.875rem;">Sign in</a>
        </div>
    </div>
    @endguest

    {{-- Error banner --}}
    <div x-show="error" x-transition style="margin-bottom: 1.5rem; border-radius: 0.5rem; padding: 0.75rem 1rem; border: 1px solid rgba(185,64,64,0.3); background: rgba(185,64,64,0.08);">
        <p class="text-[var(--error)]" style="font-size: 0.875rem;" x-text="error"></p>
    </div>

    {{-- Location input --}}
    <div x-show="status !== 'loading'" class="glass" style="border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem; max-width: 520px;">
        <p class="text-[var(--ink)]" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem;">Where are you right now?</p>

        <button @click="useLocation()" class="btn-primary" style="width: 100%; justify-content: center; margin-bottom: 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width: 16px; height: 16px; margin-right: 8px; flex-shrink: 0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
            Use my location
        </button>

        <div style="position: relative; margin-bottom: 1rem;">
            <div style="position: absolute; inset: 0; display: flex; align-items: center;">
                <div style="width: 100%; border-top: 1px solid var(--hairline);"></div>
            </div>
            <div style="position: relative; display: flex; justify-content: center;">
                <span class="text-[var(--muted)]" style="background: var(--surface-bg); padding: 0 0.75rem; font-size: 0.75rem;">or enter a city</span>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <input type="text" x-model="city" @keydown.enter="useCity()" placeholder="e.g. Makati, Quezon City, Taguig" class="input-field" style="flex: 1;">
            <button @click="useCity()" class="btn-primary" style="padding: 0 1.25rem;">Go</button>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="status === 'loading'" x-transition class="flex flex-col gap-6">
        <div class="glass animate-pulse rounded-2xl p-6">
            <div class="bg-[var(--shadow-dark)] h-3 w-24 rounded mb-4"></div>
            <div class="bg-[var(--shadow-dark)] h-12 w-44 rounded mb-3"></div>
            <div class="flex gap-6">
                <div class="bg-[var(--shadow-dark)] h-3.5 w-16 rounded"></div>
                <div class="bg-[var(--shadow-dark)] h-3.5 w-20 rounded"></div>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="i in 3" :key="i">
                <div class="neu-raised animate-pulse rounded-2xl overflow-hidden">
                    <div class="bg-[var(--shadow-dark)] h-52"></div>
                    <div class="p-4 flex flex-col gap-3">
                        <div class="bg-[var(--shadow-dark)] h-3.5 w-3/4 rounded"></div>
                        <div class="bg-[var(--shadow-dark)] h-3 w-1/2 rounded"></div>
                        <div class="bg-[var(--shadow-dark)] h-2 w-full rounded"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Results --}}
    <div x-show="status === 'done'" x-transition>
    <div style="display: flex; flex-direction: column; gap: 2.5rem;">

        {{-- Weather card --}}
        <div x-show="weather" class="glass" style="border-radius: 1.25rem; overflow: hidden;">

            {{-- Two-column hero --}}
            <div style="display: flex; align-items: stretch;">

                {{-- Left panel: temperature hero --}}
                <div style="flex: 1; padding: 1.75rem 2rem; display: flex; flex-direction: column; border-right: 1px solid var(--hairline); min-width: 0;">

                    {{-- Condition icon + description + season --}}
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <img
                            x-show="weather?.icon_code"
                            :src="'https://openweathermap.org/img/wn/' + weather?.icon_code + '@2x.png'"
                            :alt="weather?.condition"
                            style="width: 52px; height: 52px; flex-shrink: 0;"
                        >
                        <div>
                            <p class="text-[var(--ink)]" style="font-size: 0.95rem; font-weight: 500; line-height: 1.2;" x-text="weather?.description"></p>
                            <p class="text-[var(--muted)]" style="font-size: 0.7rem; margin-top: 3px; text-transform: capitalize;" x-text="seasonEmoji(weather?.season) + '  ' + weather?.season"></p>
                        </div>
                    </div>

                    {{-- Giant temperature — °C sits at bottom-right of number --}}
                    <div style="display: flex; align-items: flex-end; gap: 0.1rem;">
                        <span class="font-display text-[var(--ink)]" style="font-size: 6.5rem; font-weight: 300; letter-spacing: -0.04em; line-height: 1;" x-text="weather?.temperature"></span>
                        <span class="font-display text-[var(--muted)]" style="font-size: 1.75rem; font-weight: 300; padding-bottom: 0.6rem;">°C</span>
                    </div>
                </div>

                {{-- Right panel: location + detail rows --}}
                <div style="flex: 1; padding: 1.75rem 1.75rem; display: flex; flex-direction: column; min-width: 0;">

                    {{-- Location --}}
                    <div style="display: flex; align-items: flex-start; gap: 0.375rem; margin-bottom: 1.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px; flex-shrink: 0; margin-top: 2px; color: var(--color-accent);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <p class="text-[var(--ink)]" style="font-size: 0.875rem; font-weight: 500; line-height: 1.3;" x-text="weather?.location"></p>
                    </div>

                    {{-- Detail rows — fixed label width keeps values immediately to the right --}}
                    <div style="display: flex; flex-direction: column;">
                        <template x-for="row in [
                            { label: 'Feels like', value: weather?.feels_like + '°C' },
                            { label: 'High / Low',  value: weather?.temp_max + '° / ' + weather?.temp_min + '°' },
                            { label: 'Humidity',    value: weather?.humidity + '%' },
                            { label: 'Wind',        value: weather?.wind_speed + ' m/s' },
                            { label: 'Pressure',    value: weather?.pressure ? weather?.pressure + ' hPa' : '—' },
                        ]" :key="row.label">
                            <div style="display: flex; align-items: baseline; gap: 1rem; padding: 0.4rem 0; border-bottom: 1px solid var(--hairline);">
                                <span class="text-[var(--muted)]" style="min-width: 5.5rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.09em; flex-shrink: 0;" x-text="row.label"></span>
                                <span class="text-[var(--ink)]" style="font-size: 0.875rem; font-weight: 400;" x-text="row.value"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Bottom strip: secondary stats --}}
            <div style="border-top: 1px solid var(--hairline); display: flex;">
                <div style="flex: 1; padding: 1.1rem 1.25rem; text-align: center; border-right: 1px solid var(--hairline);">
                    <p class="text-[var(--muted)]" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.09em; margin-bottom: 6px;">Visibility</p>
                    <p class="text-[var(--ink)]" style="font-size: 0.875rem; font-weight: 500;" x-text="weather?.visibility_km != null ? weather.visibility_km + ' km' : '—'"></p>
                </div>
                <div style="flex: 1; padding: 1.1rem 1.25rem; text-align: center; border-right: 1px solid var(--hairline);">
                    <p class="text-[var(--muted)]" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.09em; margin-bottom: 6px;">Cloud cover</p>
                    <p class="text-[var(--ink)]" style="font-size: 0.875rem; font-weight: 500;" x-text="weather?.clouds != null ? weather.clouds + '%' : '—'"></p>
                </div>
                <div style="flex: 1; padding: 1.1rem 1.25rem; text-align: center;">
                    <p class="text-[var(--muted)]" style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.09em; margin-bottom: 6px;">Wind dir.</p>
                    <p class="text-[var(--ink)]" style="font-size: 0.875rem; font-weight: 500;" x-text="weather?.wind_deg != null ? weather.wind_deg + '°' : '—'"></p>
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        <div x-show="recommendations.length === 0" class="neu-raised" style="border-radius: 1rem; padding: 2rem; text-align: center;">
            <template x-if="isGuest">
                <div>
                    <p class="text-[var(--ink)]" style="font-weight: 500; margin-bottom: 0.5rem;">No recommendations found</p>
                    <p class="text-[var(--muted)]" style="font-size: 0.875rem; margin-bottom: 1rem;">Our catalog may not have fragrances matching this climate yet. Try a different location.</p>
                </div>
            </template>
            <template x-if="!isGuest">
                <div>
                    <p class="text-[var(--ink)]" style="font-weight: 500; margin-bottom: 0.5rem;">Your wardrobe is empty</p>
                    <p class="text-[var(--muted)]" style="font-size: 0.875rem; margin-bottom: 1rem;">Add some fragrances in Browse to get recommendations.</p>
                    <a href="{{ route('browse') }}" class="btn-primary" style="display: inline-flex;">Browse Fragrances</a>
                </div>
            </template>
        </div>

        {{-- Recommendation cards — x-show on wrapper, grid on inner div --}}
        <div x-show="recommendations.length > 0">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="(rec, index) in recommendations" :key="rec.fragrance_id">
                    <div class="neu-raised" style="border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column;">

                        {{-- Image --}}
                        <div style="height: 180px; background: var(--bg); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img
                                x-show="rec.image_url"
                                :src="rec.image_url"
                                :alt="rec.name"
                                style="width: 100%; height: 100%; object-fit: contain; padding: 0.75rem;"
                                x-on:error="rec.image_url = null"
                            >
                            <span
                                x-show="!rec.image_url"
                                class="font-display text-[var(--muted)]"
                                style="font-size: 3rem; font-weight: 300;"
                                x-text="(rec.brand || '?')[0]"
                            ></span>
                            <div style="position: absolute; top: 8px; left: 8px; width: 24px; height: 24px; border-radius: 50%; background: var(--color-accent); display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 0.65rem; font-weight: 600; color: #1A1A1A;" x-text="'#' + (index + 1)"></span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div style="padding: 0.875rem; display: flex; flex-direction: column; gap: 0.625rem; flex: 1;">

                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                                <div style="min-width: 0;">
                                    <p class="font-display text-[var(--ink)]" style="font-size: 1rem; font-weight: 300; line-height: 1.2;" x-text="rec.name"></p>
                                    <p class="text-[var(--muted)]" style="font-size: 0.7rem; margin-top: 2px;" x-text="rec.brand"></p>
                                </div>
                                <span class="font-display text-[var(--color-accent)]" style="font-size: 1rem; font-weight: 500; flex-shrink: 0; letter-spacing: -0.01em;" x-text="Math.round(rec.score * 100) + '%'"></span>
                            </div>

                            <div style="height: 4px; width: 100%; border-radius: 9999px; background: var(--shadow-dark); overflow: hidden;">
                                <div style="height: 100%; border-radius: 9999px; background: var(--color-accent); transition: width 0.7s ease;" :style="'width: ' + Math.round(rec.score * 100) + '%'"></div>
                            </div>

                            <p class="text-[var(--muted)]" style="font-size: 0.7rem; font-style: italic; line-height: 1.5;" x-text="rec.reasoning"></p>

                            <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                <template x-for="(note, ni) in [...new Set(rec.matched_notes || [])].slice(0, 4)" :key="ni">
                                    <span class="text-[var(--muted)]" style="font-size: 0.65rem; padding: 1px 7px; border-radius: 9999px; border: 1px solid var(--hairline);" x-text="note"></span>
                                </template>
                            </div>

                            <div style="margin-top: auto; padding-top: 0.25rem;">
                                {{-- Auth users: record their choice. Guests: nudge to sign in. --}}
                                <template x-if="!isGuest">
                                    <button
                                        @click="choose(rec.fragrance_id)"
                                        :disabled="chosenId !== null"
                                        :class="chosenId === rec.fragrance_id ? 'btn-primary' : 'btn-ghost'"
                                        style="width: 100%; font-size: 0.75rem; padding: 0.4rem 0.75rem;"
                                        x-text="chosenId === rec.fragrance_id ? '✓ Wearing this today' : 'I wore this'"
                                    ></button>
                                </template>
                                <template x-if="isGuest">
                                    <a href="{{ route('register') }}" class="btn-ghost" style="display: block; width: 100%; text-align: center; font-size: 0.75rem; padding: 0.4rem 0.75rem;">
                                        Sign in to track
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Recommended Pick-Ups --}}
        <div x-show="pickUps.length > 0" x-transition>
            <div style="margin-bottom: 1rem;">
                <h2 class="font-display text-[var(--ink)]" style="font-size: 1.25rem; font-weight: 300;">Recommended Pick-Ups</h2>
                <p class="text-[var(--muted)]" style="font-size: 0.75rem; margin-top: 0.25rem;">Fragrances outside your wardrobe that suit today's weather and match your taste profile.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="rec in pickUps" :key="rec.fragrance_id">
                    <div class="glass" style="border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; border: 1px solid var(--hairline);">

                        {{-- Image --}}
                        <div style="height: 160px; background: var(--bg); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <img
                                x-show="rec.image_url"
                                :src="rec.image_url"
                                :alt="rec.name"
                                style="width: 100%; height: 100%; object-fit: contain; padding: 0.75rem;"
                                x-on:error="rec.image_url = null"
                            >
                            <span
                                x-show="!rec.image_url"
                                class="font-display text-[var(--muted)]"
                                style="font-size: 2.5rem; font-weight: 300;"
                                x-text="(rec.brand || '?')[0]"
                            ></span>
                        </div>

                        {{-- Info --}}
                        <div style="padding: 0.875rem; display: flex; flex-direction: column; gap: 0.5rem; flex: 1;">
                            <div>
                                <p class="font-display text-[var(--ink)]" style="font-size: 0.95rem; font-weight: 300; line-height: 1.2;" x-text="rec.name"></p>
                                <p class="text-[var(--muted)]" style="font-size: 0.7rem; margin-top: 2px;" x-text="rec.brand"></p>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; height: 3px; border-radius: 9999px; background: var(--shadow-dark); overflow: hidden;">
                                    <div style="height: 100%; border-radius: 9999px; background: var(--color-accent); transition: width 0.7s ease;" :style="'width: ' + Math.round(rec.score * 100) + '%'"></div>
                                </div>
                                <span class="text-[var(--color-accent)]" style="font-size: 0.75rem; font-weight: 500; flex-shrink: 0;" x-text="Math.round(rec.score * 100) + '%'"></span>
                            </div>

                            <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                <template x-for="(note, ni) in [...new Set(rec.matched_notes || [])].slice(0, 3)" :key="ni">
                                    <span class="text-[var(--muted)]" style="font-size: 0.65rem; padding: 1px 7px; border-radius: 9999px; border: 1px solid var(--hairline);" x-text="note"></span>
                                </template>
                            </div>

                            <div style="margin-top: auto; padding-top: 0.375rem; display: flex; gap: 0.5rem;">
                                <a
                                    :href="'{{ url('/fragrances') }}/' + rec.fragrance_id"
                                    class="btn-ghost"
                                    style="flex: 1; text-align: center; font-size: 0.7rem; padding: 0.35rem 0.5rem;"
                                >View details</a>
                                <template x-if="rec.external_source_url">
                                    <a
                                        :href="rec.external_source_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn-primary"
                                        style="flex: 1; text-align: center; font-size: 0.7rem; padding: 0.35rem 0.5rem;"
                                    >Buy / Info ↗</a>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Re-fetch --}}
        <div style="display: flex; justify-content: center; padding-top: 0.5rem;">
            <button
                @click="status = 'idle'; recommendations = []; pickUps = []; weather = null; chosenId = null; logId = null; error = null;"
                class="btn-ghost"
                style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Check again
            </button>
        </div>

    </div>
    </div>

</div>
@endsection
