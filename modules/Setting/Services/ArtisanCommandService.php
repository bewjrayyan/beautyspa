<?php

namespace Modules\Setting\Services;

use AestheticCart\Updater;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use RuntimeException;

class ArtisanCommandService
{
    /**
     * @return array<string, array{command?: string, parameters?: array<string, mixed>, handler?: string, confirm?: bool}>
     */
    public function definitions(): array
    {
        return [
            'optimize_clear' => [
                'command' => 'optimize:clear',
                'confirm' => false,
            ],
            'migrate' => [
                'command' => 'migrate',
                'parameters' => ['--force' => true],
                'confirm' => true,
            ],
            'sync_translations' => [
                'command' => 'translation:refresh-cache',
                'parameters' => ['--sync' => true],
                'confirm' => false,
            ],
            'view_clear' => [
                'command' => 'view:clear',
                'confirm' => false,
            ],
            'post_update' => [
                'handler' => 'postUpdate',
                'confirm' => true,
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, description: string, confirm: bool}>
     */
    public function buttons(): array
    {
        return collect($this->definitions())
            ->map(function (array $definition, string $key) {
                return [
                    'key' => $key,
                    'label' => trans('setting::settings.form.artisan_commands.'.$key.'.label'),
                    'description' => trans('setting::settings.form.artisan_commands.'.$key.'.description'),
                    'confirm' => (bool) ($definition['confirm'] ?? false),
                    'confirm_message' => trans('setting::settings.form.artisan_commands.'.$key.'.confirm'),
                ];
            })
            ->values()
            ->all();
    }

    public function run(string $action): string
    {
        $definitions = $this->definitions();

        if (! isset($definitions[$action])) {
            throw new InvalidArgumentException(trans('setting::settings.form.artisan_command_invalid'));
        }

        $definition = $definitions[$action];

        if (($definition['handler'] ?? null) === 'postUpdate') {
            if (! config('app.installed')) {
                throw new RuntimeException(trans('setting::settings.form.artisan_command_not_installed'));
            }

            Updater::run();

            return trans('setting::messages.artisan_post_update_success');
        }

        Artisan::call(
            (string) $definition['command'],
            $definition['parameters'] ?? []
        );

        $output = trim(Artisan::output());

        return $output !== ''
            ? trans('setting::messages.artisan_command_success_with_output', ['output' => $output])
            : trans('setting::messages.artisan_command_success', [
                'command' => $definition['command'],
            ]);
    }
}
