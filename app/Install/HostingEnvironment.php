<?php

namespace AestheticCart\Install;

class HostingEnvironment
{
    public function suggestedAppUrl(): string
    {
        $scheme = request()->isSecure() ? 'https' : 'http';
        $host = request()->getHost();
        $path = trim(request()->getBaseUrl(), '/');

        return rtrim("{$scheme}://{$host}".($path !== '' ? "/{$path}" : ''), '/');
    }

    /**
     * @return array<string, bool|string>
     */
    public function profile(): array
    {
        return [
            'document_root' => $this->documentRootType(),
            'suggested_app_url' => $this->suggestedAppUrl(),
            'php_version' => PHP_VERSION,
        ];
    }

    public function documentRootType(): string
    {
        $publicIndex = public_path('index.php');
        $rootIndex = base_path('index.php');
        $script = (string) ($_SERVER['SCRIPT_FILENAME'] ?? '');

        if ($script !== '' && realpath($script) === realpath($publicIndex)) {
            return 'public';
        }

        if ($script !== '' && realpath($script) === realpath($rootIndex)) {
            return 'root';
        }

        return 'unknown';
    }

    /**
     * @return array<string, bool>
     */
    public function uploadChecks(): array
    {
        return [
            'vendor' => is_file(base_path('vendor/autoload.php')),
            'build_assets' => is_file(public_path('build/manifest.json')),
            'env_example' => is_file(base_path('.env.example')),
        ];
    }
}
