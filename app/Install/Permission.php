<?php

namespace AestheticCart\Install;

class Permission
{
    public function __construct(
        private EnvironmentBootstrap $bootstrap
    ) {
    }

    public function provided(): bool
    {
        return collect($this->files())
            ->merge($this->directories())
            ->every(fn ($item) => $item);
    }

    public function prepare(): void
    {
        $this->bootstrap->ensureEnvFileExists();
    }


    public function files(): array
    {
        return [
            '.env (writable)' => $this->bootstrap->isEnvWritable() || $this->bootstrap->canCreateEnv(),
        ];
    }


    public function directories(): array
    {
        return [
            'storage' => is_writable(storage_path()),
            'storage/framework/sessions' => is_writable(storage_path('framework/sessions')),
            'bootstrap/cache' => is_writable(app()->bootstrapPath('cache')),
        ];
    }
}
