<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service: EngineService
 *
 * Laravel's bridge to the Python recommendation engine.
 * Supports two invocation modes, switchable via ENGINE_MODE in .env:
 *
 * MODE 1 — 'subprocess' (default, development-friendly)
 * ──────────────────────────────────────────────────────
 * Spawns a Python process for each recommendation request using proc_open()
 * with stdin/stdout pipes. The JSON payload is written to stdin; the scored
 * results are read from stdout. proc_open() is used instead of shell_exec()
 * to avoid passing JSON data through shell arguments (injection-safe).
 *
 *   Laravel → proc_open(python engine.py) → stdin pipe → Python
 *   Python → stdout pipe → Laravel reads JSON → parses result
 *
 * Pros:  Simple setup, no extra server process to manage.
 * Cons:  Python startup overhead per request (~150–300ms on first call).
 *        Acceptable for dev; not ideal for production at scale.
 *
 * MODE 2 — 'microservice' (recommended for production)
 * ─────────────────────────────────────────────────────
 * The Python engine runs as a persistent FastAPI server (see engine/README.md).
 * Laravel calls it over HTTP — no subprocess overhead per request.
 *
 *   Start engine: cd engine && python recommendation_engine.py --serve
 *   Configure:    ENGINE_MODE=microservice
 *                 ENGINE_MICROSERVICE_URL=http://127.0.0.1:8001
 *
 *   Laravel → HTTP POST /recommend → FastAPI engine → JSON response
 *
 * Pros:  Fast (no Python startup cost). Horizontally scalable.
 * Cons:  Requires the engine process to be running separately.
 *
 * Error Handling
 * ──────────────
 * Both modes throw \RuntimeException on failure. The RecommendationController
 * catches this and returns a 503 Service Unavailable to the Flutter app.
 * All errors are logged to storage/logs/laravel.log for debugging.
 *
 * @see engine/recommendation_engine.py  Python entry point
 * @see engine/scorer.py                 Scoring algorithm
 * @see engine/models.py                 Input/output Pydantic models
 */
class EngineService
{
    /** Execution mode: 'subprocess' or 'microservice' */
    private string $mode;

    /** Absolute path to the Python executable */
    private string $pythonBin;

    /** Absolute path to the engine script */
    private string $scriptPath;

    /** Microservice base URL (mode 2 only) */
    private string $microserviceUrl;

    /** Subprocess timeout in seconds */
    private int $timeout;

    public function __construct()
    {
        $this->mode            = config('services.engine.mode', 'subprocess');
        $this->pythonBin       = config('services.engine.python_bin', 'python');
        $this->scriptPath      = config('services.engine.script_path');
        $this->microserviceUrl = rtrim((string) config('services.engine.microservice_url', ''), '/');
        $this->timeout         = (int) config('services.engine.timeout', 30);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Run the recommendation engine with the given payload.
     *
     * Dispatches to the configured mode (subprocess or microservice).
     * Returns the full ranked list of scored fragrances as a PHP array.
     *
     * @param  array<string, mixed>  $payload  EngineRequest shape:
     *                                          { weather: {...}, collection: [...] }
     * @return list<array<string, mixed>>  Ranked ScoredFragrance array.
     *
     * @throws \RuntimeException  If the engine fails, times out, or returns invalid JSON.
     */
    public function run(array $payload): array
    {
        return match ($this->mode) {
            'microservice' => $this->runAsMicroservice($payload),
            default        => $this->runAsSubprocess($payload),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subprocess Mode
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Invoke the Python engine as a subprocess via proc_open().
     *
     * Security note: The JSON payload is passed through a stdin pipe, NOT
     * as a shell argument. This prevents JSON data from being interpreted
     * as shell commands regardless of what it contains.
     *
     * Process flow:
     *   1. Open a Python process with stdin/stdout/stderr pipes
     *   2. Write JSON payload to the process's stdin pipe
     *   3. Close stdin to signal EOF — the engine reads until EOF
     *   4. Read stdout (results) and stderr (errors) with stream_select timeout
     *   5. Wait for process to exit and check the exit code
     *   6. Parse and return the JSON from stdout
     *
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     *
     * @throws \RuntimeException
     */
    private function runAsSubprocess(array $payload): array
    {
        if (empty($this->scriptPath) || ! file_exists($this->scriptPath)) {
            throw new \RuntimeException(
                "Engine script not found at: '{$this->scriptPath}'. " .
                "Check ENGINE_SCRIPT_PATH in .env."
            );
        }

        $descriptors = [
            0 => ['pipe', 'r'],  // stdin  — we write the payload here
            1 => ['pipe', 'w'],  // stdout — engine writes results here
            2 => ['pipe', 'w'],  // stderr — engine writes errors here
        ];

        // Launch the Python process. Using an array avoids shell interpretation.
        $process = proc_open(
            [$this->pythonBin, $this->scriptPath],
            $descriptors,
            $pipes,
            dirname($this->scriptPath), // Working directory = engine/
        );

        if (! is_resource($process)) {
            throw new \RuntimeException(
                "Failed to spawn the Python engine process. " .
                "Ensure '{$this->pythonBin}' is in PATH."
            );
        }

        // ── Write payload to stdin ────────────────────────────────────────
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        fwrite($pipes[0], $json);
        fclose($pipes[0]); // Close stdin → signals EOF to the Python script

        // ── Read stdout and stderr with timeout ───────────────────────────
        // stream_select() monitors both pipes simultaneously so we don't
        // block indefinitely if the process hangs.
        [$stdout, $stderr] = $this->readPipesWithTimeout(
            $pipes[1],
            $pipes[2],
            $this->timeout
        );

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // ── Handle non-zero exit ──────────────────────────────────────────
        if ($exitCode !== 0) {
            $errorDetail = $this->parseStderr($stderr);
            Log::error('[EngineService] Python engine exited with non-zero code', [
                'exit_code' => $exitCode,
                'error'     => $errorDetail,
                'stderr'    => $stderr,
            ]);
            throw new \RuntimeException(
                "The recommendation engine failed (exit {$exitCode}): {$errorDetail}"
            );
        }

        // ── Parse and validate stdout ─────────────────────────────────────
        return $this->parseEngineOutput($stdout);
    }

    /**
     * Read from two file handles simultaneously with a timeout.
     *
     * Uses stream_select() to avoid blocking if one pipe has no data.
     * Accumulates output from both handles until they are closed by the process.
     *
     * @param  resource  $stdoutPipe
     * @param  resource  $stderrPipe
     * @param  int       $timeoutSeconds
     * @return array{0: string, 1: string}  [stdout_content, stderr_content]
     *
     * @throws \RuntimeException  If the timeout is exceeded.
     */
    private function readPipesWithTimeout($stdoutPipe, $stderrPipe, int $timeoutSeconds): array
    {
        stream_set_blocking($stdoutPipe, false);
        stream_set_blocking($stderrPipe, false);

        $stdout  = '';
        $stderr  = '';
        $start   = time();

        while (! feof($stdoutPipe) || ! feof($stderrPipe)) {
            if ((time() - $start) > $timeoutSeconds) {
                throw new \RuntimeException(
                    "The recommendation engine timed out after {$timeoutSeconds} seconds."
                );
            }

            $readPipes = array_filter([$stdoutPipe, $stderrPipe], fn ($p) => ! feof($p));

            if (empty($readPipes)) {
                break;
            }

            $write     = null;
            $except    = null;
            $changed   = stream_select($readPipes, $write, $except, 1);

            if ($changed === false) {
                break;
            }

            if (! feof($stdoutPipe)) {
                $chunk = fread($stdoutPipe, 8192);
                if ($chunk !== false) {
                    $stdout .= $chunk;
                }
            }

            if (! feof($stderrPipe)) {
                $chunk = fread($stderrPipe, 8192);
                if ($chunk !== false) {
                    $stderr .= $chunk;
                }
            }
        }

        return [$stdout, $stderr];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Microservice Mode
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Call the FastAPI microservice over HTTP.
     *
     * The engine must be running before making requests in this mode.
     * Start it with: python engine/recommendation_engine.py --serve
     *
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     *
     * @throws \RuntimeException
     */
    private function runAsMicroservice(array $payload): array
    {
        if (empty($this->microserviceUrl)) {
            throw new \RuntimeException(
                "ENGINE_MICROSERVICE_URL is not configured. " .
                "Set it in .env when using ENGINE_MODE=microservice."
            );
        }

        try {
            $response = Http::timeout($this->timeout)
                            ->connectTimeout(5)
                            ->post($this->microserviceUrl . '/recommend', $payload);

            if (! $response->successful()) {
                $body = $response->body();
                Log::error('[EngineService] Microservice returned error', [
                    'status' => $response->status(),
                    'body'   => $body,
                ]);
                throw new \RuntimeException(
                    "The recommendation engine returned HTTP {$response->status()}."
                );
            }

            $results = $response->json();

            if (! is_array($results)) {
                throw new \RuntimeException(
                    "The recommendation engine returned an unexpected response format."
                );
            }

            return $results;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[EngineService] Cannot connect to microservice: ' . $e->getMessage());
            throw new \RuntimeException(
                "Cannot connect to the recommendation engine microservice. " .
                "Ensure it is running at {$this->microserviceUrl}. " .
                "Start with: python engine/recommendation_engine.py --serve"
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shared Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parse the engine's stdout JSON into a PHP array.
     *
     * @param  string  $stdout  Raw stdout content from the engine process
     * @return list<array<string, mixed>>
     *
     * @throws \RuntimeException  If stdout is empty or not valid JSON.
     */
    private function parseEngineOutput(string $stdout): array
    {
        $stdout = trim($stdout);

        if (empty($stdout)) {
            throw new \RuntimeException(
                "The recommendation engine produced no output. " .
                "Check that the engine script path is correct."
            );
        }

        try {
            $results = json_decode($stdout, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('[EngineService] Engine stdout is not valid JSON', [
                'stdout' => substr($stdout, 0, 500),
            ]);
            throw new \RuntimeException(
                "The recommendation engine returned malformed JSON: {$e->getMessage()}"
            );
        }

        if (! is_array($results)) {
            throw new \RuntimeException(
                "The recommendation engine returned an unexpected response structure."
            );
        }

        return $results;
    }

    /**
     * Extract a human-readable error message from the engine's stderr output.
     *
     * The engine writes structured JSON to stderr on errors:
     * { "error": "ValidationError", "detail": "..." }
     *
     * Falls back to the raw stderr string if it is not valid JSON.
     *
     * @param  string  $stderr  Raw stderr content
     * @return string  Human-readable error detail
     */
    private function parseStderr(string $stderr): string
    {
        $stderr = trim($stderr);

        if (empty($stderr)) {
            return 'No error details available.';
        }

        try {
            $parsed = json_decode($stderr, associative: true, flags: JSON_THROW_ON_ERROR);
            return $parsed['detail'] ?? $parsed['error'] ?? $stderr;
        } catch (\JsonException) {
            // stderr wasn't JSON — return it as-is, truncated for log safety
            return substr($stderr, 0, 500);
        }
    }
}
