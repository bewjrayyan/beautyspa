<?php

namespace AestheticCart\Install;

use RuntimeException;

class EnvironmentBootstrap
{
    public function envExists(): bool
    {
        return is_file(base_path('.env'));
    }

    public function canCreateEnv(): bool
    {
        return ! $this->envExists()
            && is_file(base_path('.env.example'))
            && is_writable(base_path());
    }

    public function isEnvWritable(): bool
    {
        return $this->envExists() && is_writable(base_path('.env'));
    }

    public function ensureEnvFileExists(): bool
    {
        if ($this->envExists()) {
            return $this->isEnvWritable();
        }

        if (! $this->canCreateEnv()) {
            return false;
        }

        if (! copy(base_path('.env.example'), base_path('.env'))) {
            throw new RuntimeException('Could not create .env from .env.example.');
        }

        return is_writable(base_path('.env'));
    }
}
