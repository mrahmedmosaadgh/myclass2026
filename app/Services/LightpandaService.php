<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * LightpandaService
 *
 * Integrates the Lightpanda headless browser binary for web scraping
 * and page content extraction tasks.
 *
 * Install Lightpanda:
 *   curl -fsSL https://pkg.lightpanda.io/install.sh | bash
 *
 * Set LIGHTPANDA_BINARY in .env (default: lightpanda)
 */
class LightpandaService
{
    protected string $binary;
    protected int $timeout;

    public function __construct()
    {
        $this->binary  = config('services.lightpanda.binary', 'lightpanda');
        $this->timeout = (int) config('services.lightpanda.timeout', 30);
    }

    /**
     * Fetch the rendered HTML of a URL after JavaScript execution.
     *
     * @param  string $url
     * @return array{html: string|null, error: string|null}
     */
    public function fetchHtml(string $url): array
    {
        return $this->run('fetch', $url);
    }

    /**
     * Extract plain text content from a URL (strips HTML tags).
     *
     * @param  string $url
     * @return array{text: string|null, error: string|null}
     */
    public function extractText(string $url): array
    {
        $result = $this->fetchHtml($url);

        if ($result['error']) {
            return ['text' => null, 'error' => $result['error']];
        }

        $text = strip_tags($result['html'] ?? '');
        $text = preg_replace('/\s+/', ' ', $text);

        return ['text' => trim($text), 'error' => null];
    }

    /**
     * Run a Lightpanda command and return stdout/stderr.
     *
     * @param  string $command  Lightpanda sub-command (e.g. 'fetch')
     * @param  string $url
     * @return array{html: string|null, error: string|null}
     */
    protected function run(string $command, string $url): array
    {
        $escapedUrl = escapeshellarg($url);
        $cmd        = "{$this->binary} {$command} {$escapedUrl}";

        Log::info('[Lightpanda] Running command', ['cmd' => $cmd]);

        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (! is_resource($process)) {
            return ['html' => null, 'error' => 'Failed to start Lightpanda process. Is it installed?'];
        }

        fclose($pipes[0]);

        // Apply timeout via stream_select
        $stdout = '';
        $stderr = '';
        $start  = time();

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (true) {
            $read   = [$pipes[1], $pipes[2]];
            $write  = null;
            $except = null;

            $changed = stream_select($read, $write, $except, 1);

            if ($changed === false) {
                break;
            }

            foreach ($read as $stream) {
                $chunk = fread($stream, 8192);
                if ($chunk !== false) {
                    if ($stream === $pipes[1]) {
                        $stdout .= $chunk;
                    } else {
                        $stderr .= $chunk;
                    }
                }
            }

            if (feof($pipes[1]) && feof($pipes[2])) {
                break;
            }

            if ((time() - $start) >= $this->timeout) {
                proc_terminate($process);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return ['html' => null, 'error' => "Lightpanda timed out after {$this->timeout}s"];
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            Log::warning('[Lightpanda] Non-zero exit', ['code' => $exitCode, 'stderr' => $stderr]);

            return ['html' => null, 'error' => trim($stderr) ?: "Process exited with code {$exitCode}"];
        }

        return ['html' => $stdout, 'error' => null];
    }
}
