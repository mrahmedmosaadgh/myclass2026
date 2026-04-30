<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use JsonException;

class PuppeteerPdfService
{
    protected string $nodeBinary;
    protected string $scriptPath;
    protected string $workingDir;
    protected int $timeout;
    protected int $maxPages;
    protected bool $strictEnvironmentCheck;
    protected string $bindingPath;
    protected string $layoutVersion;
    protected string $metricsPath;

    public function __construct()
    {
        $this->nodeBinary = (string) config('services.print_pdf.node_binary', 'node');
        $this->scriptPath = (string) config('services.print_pdf.script_path', base_path('tools/print-calibration/render-pdf.js'));
        $this->workingDir = (string) config('services.print_pdf.working_dir', base_path('tools/print-calibration'));
        $this->timeout = (int) config('services.print_pdf.timeout', 60);
        $this->maxPages = (int) config('services.print_pdf.max_pages', 60);
        $this->strictEnvironmentCheck = (bool) config('services.print_pdf.strict_environment_check', true);
        $this->bindingPath = (string) config('services.print_pdf.binding_path', base_path('tools/print-calibration/layout-metrics-binding.json'));
        $this->layoutVersion = (string) config('services.print_pdf.layout_version', 'v1.0');
        $this->metricsPath = (string) config('services.print_pdf.metrics_path', base_path('tools/print-calibration/metrics.v1.json'));
    }

    public function renderHtmlToPdf(string $html): array
    {
        if (!is_file($this->scriptPath)) {
            return ['pdf' => null, 'error' => "Puppeteer script not found: {$this->scriptPath}", 'meta' => null];
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $htmlFile = tempnam($tmpDir, 'print_html_');
        $pdfFile = tempnam($tmpDir, 'print_pdf_');
        $pdfFileWithExt = $pdfFile . '.pdf';

        if ($htmlFile === false || $pdfFile === false) {
            return ['pdf' => null, 'error' => 'Failed to create temp files for Puppeteer PDF generation', 'meta' => null];
        }

        @rename($pdfFile, $pdfFileWithExt);

        try {
            file_put_contents($htmlFile, $html);

            $cmd = sprintf(
                '%s %s --input=%s --output=%s --maxPages=%d',
                escapeshellcmd($this->nodeBinary),
                escapeshellarg($this->scriptPath),
                escapeshellarg($htmlFile),
                escapeshellarg($pdfFileWithExt),
                $this->maxPages
            );

            $result = $this->runProcess($cmd, $this->workingDir, $this->timeout);

            if ($result['error']) {
                return ['pdf' => null, 'error' => $result['error'], 'meta' => null];
            }

            $meta = json_decode($result['stdout'], true);
            if (!is_array($meta)) {
                return ['pdf' => null, 'error' => 'Invalid Puppeteer JSON metadata output', 'meta' => null];
            }

            $envError = $this->validateEnvironmentAgainstMetrics($meta);
            if ($envError !== null) {
                return ['pdf' => null, 'error' => $envError, 'meta' => $meta];
            }

            if (!is_file($pdfFileWithExt)) {
                return ['pdf' => null, 'error' => 'Puppeteer did not produce a PDF file', 'meta' => $meta];
            }

            $pdf = file_get_contents($pdfFileWithExt);
            if ($pdf === false || $pdf === '') {
                return ['pdf' => null, 'error' => 'Generated PDF is empty', 'meta' => $meta];
            }

            return ['pdf' => $pdf, 'error' => null, 'meta' => $meta];
        } catch (\Throwable $e) {
            Log::error('Puppeteer PDF generation exception', ['message' => $e->getMessage()]);
            return ['pdf' => null, 'error' => $e->getMessage(), 'meta' => null];
        } finally {
            @unlink($htmlFile);
            @unlink($pdfFileWithExt);
        }
    }

    protected function validateEnvironmentAgainstMetrics(array $meta): ?string
    {
        if (!$this->strictEnvironmentCheck) {
            return null;
        }

        [$metrics, $metricsError] = $this->loadValidatedMetricsRuntime();
        if ($metricsError !== null) {
            return $metricsError;
        }

        $expected = $metrics['environment'] ?? null;

        if (!is_array($expected)) {
            return 'Metrics environment fingerprint is missing or invalid';
        }

        $expectedPuppeteer = (string) ($expected['puppeteerVersion'] ?? '');
        $expectedChromium = (string) ($expected['chromiumVersion'] ?? '');

        $actualPuppeteer = (string) ($meta['puppeteerVersion'] ?? '');
        $actualChromium = (string) ($meta['chromiumVersion'] ?? '');

        if ($expectedPuppeteer !== '' && $actualPuppeteer !== $expectedPuppeteer) {
            return "Puppeteer version mismatch: expected {$expectedPuppeteer}, got {$actualPuppeteer}";
        }

        if ($expectedChromium !== '' && $actualChromium !== $expectedChromium) {
            return "Chromium version mismatch: expected {$expectedChromium}, got {$actualChromium}";
        }

        return null;
    }

    protected function loadValidatedMetricsRuntime(): array
    {
        if (!is_file($this->bindingPath)) {
            return [null, "Binding file not found for PDF runtime validation: {$this->bindingPath}"];
        }

        $bindingData = json_decode((string) file_get_contents($this->bindingPath), true);
        if (!is_array($bindingData)) {
            return [null, 'Binding file is invalid JSON'];
        }

        $metricsVersion = $bindingData['layoutMetricsBinding'][$this->layoutVersion] ?? null;
        if (!is_string($metricsVersion) || $metricsVersion === '') {
            return [null, "No metrics binding found for layoutVersion {$this->layoutVersion}"];
        }

        $artifact = $bindingData['lockedArtifacts'][$metricsVersion] ?? null;
        $isFrozen = $artifact['frozen'] ?? null;
        $metricsRelativeFile = $artifact['file'] ?? null;

        if ($isFrozen !== true) {
            return [null, "Locked artifact for {$metricsVersion} is not frozen"];
        }

        if (!is_string($metricsRelativeFile) || $metricsRelativeFile === '') {
            return [null, "Locked artifact missing file for metricsVersion {$metricsVersion}"];
        }

        $boundMetricsPath = base_path('tools/print-calibration/' . ltrim($metricsRelativeFile, '/'));
        if (!is_file($boundMetricsPath)) {
            return [null, "Metrics file not found for runtime validation: {$boundMetricsPath}"];
        }

        $configuredPath = realpath($this->metricsPath) ?: $this->metricsPath;
        $boundPath = realpath($boundMetricsPath) ?: $boundMetricsPath;
        if ($configuredPath !== $boundPath) {
            return [null, "Configured metrics path does not match bound artifact path: {$this->metricsPath}"];
        }

        $metricsRaw = file_get_contents($boundMetricsPath);
        $metrics = json_decode($metricsRaw ?: '', true);
        if (!is_array($metrics) || !is_array($metrics['values'] ?? null)) {
            return [null, 'Metrics file has invalid structure'];
        }

        $metricsLayoutVersion = (string) ($metrics['layoutVersion'] ?? '');
        if ($metricsLayoutVersion !== $this->layoutVersion) {
            return [null, "Metrics layoutVersion mismatch: expected {$this->layoutVersion}, got {$metricsLayoutVersion}"];
        }

        $metricsDataVersion = (string) ($metrics['metricsVersion'] ?? '');
        if ($metricsDataVersion !== $metricsVersion) {
            return [null, "Metrics version mismatch: expected {$metricsVersion}, got {$metricsDataVersion}"];
        }

        $expectedIntegrityHash = (string) ($metrics['hash'] ?? '');
        if ($expectedIntegrityHash === '') {
            return [null, 'Metrics file hash is missing'];
        }

        $computedIntegrityHash = $this->computeMetricsIntegrityHash($metrics);
        if ($computedIntegrityHash === null) {
            return [null, 'Unable to compute metrics integrity hash'];
        }

        if ($expectedIntegrityHash !== $computedIntegrityHash) {
            return [null, 'Metrics integrity hash mismatch'];
        }

        return [$metrics, null];
    }

    protected function computeMetricsIntegrityHash(array $metricsData): ?string
    {
        $payload = [
            'values' => $metricsData['values'] ?? null,
            'font' => $metricsData['font'] ?? null,
            'lineHeight' => $metricsData['lineHeight'] ?? null,
        ];

        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (!is_string($json)) {
            return null;
        }

        return 'sha256-' . hash('sha256', $json);
    }

    protected function runProcess(string $cmd, string $cwd, int $timeout): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes, $cwd);
        if (!is_resource($process)) {
            return ['stdout' => null, 'error' => 'Failed to start Puppeteer process'];
        }

        fclose($pipes[0]);

        $stdout = '';
        $stderr = '';
        $start = time();

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (true) {
            $read = [$pipes[1], $pipes[2]];
            $write = null;
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

            if ((time() - $start) >= $timeout) {
                proc_terminate($process);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return ['stdout' => null, 'error' => "Puppeteer process timed out after {$timeout}s"];
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            return ['stdout' => null, 'error' => trim($stderr) ?: "Puppeteer process failed with exit code {$exitCode}"];
        }

        return ['stdout' => trim($stdout), 'error' => null];
    }
}
