<?php

namespace AestheticCart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class CspReportController extends Controller
{
    public function store(Request $request): Response
    {
        $content = $request->getContent();

        if ($content === '' || strlen($content) > 65536) {
            return response('', 204);
        }

        $payload = json_decode($content, true);

        if (! is_array($payload)) {
            return response('', 204);
        }

        $report = $payload['csp-report'] ?? $payload['body'] ?? $payload;

        if (! is_array($report)) {
            return response('', 204);
        }

        Log::channel('security')->warning('CSP violation reported.', [
            'document_uri' => $this->safeUrl($report['document-uri'] ?? $report['documentURL'] ?? null),
            'blocked_uri' => $this->safeUrl($report['blocked-uri'] ?? $report['blockedURL'] ?? null),
            'effective_directive' => mb_substr((string) ($report['effective-directive'] ?? $report['effectiveDirective'] ?? ''), 0, 100),
            'source_file' => $this->safeUrl($report['source-file'] ?? $report['sourceFile'] ?? null),
            'line_number' => (int) ($report['line-number'] ?? $report['lineNumber'] ?? 0),
            'status_code' => (int) ($report['status-code'] ?? $report['statusCode'] ?? 0),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return response('', 204);
    }

    private function safeUrl(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (in_array($value, ['inline', 'eval', 'self'], true)) {
            return $value;
        }

        $parts = parse_url($value);

        if ($parts === false) {
            return mb_substr($value, 0, 500);
        }

        $url = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $url .= $parts['host'] ?? '';
        $url .= isset($parts['port']) ? ':'.$parts['port'] : '';
        $url .= $parts['path'] ?? '';

        return mb_substr($url, 0, 500);
    }
}
