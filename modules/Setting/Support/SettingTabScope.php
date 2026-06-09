<?php

namespace Modules\Setting\Support;

use Illuminate\Http\Request;
use Modules\Admin\Ui\Facades\TabManager;

class SettingTabScope
{
    public static function activeTab(Request $request): ?string
    {
        $tab = $request->input('settings_tab') ?: $request->query('tab');

        if (! is_string($tab) || $tab === '') {
            return null;
        }

        $tabs = TabManager::get('settings');

        if (! $tabs->tabNames()->contains($tab)) {
            return null;
        }

        return $tab;
    }


    public static function fieldsForTab(?string $tab): array
    {
        if ($tab === null) {
            return [];
        }

        return TabManager::get('settings')->fieldsForTab($tab);
    }


    public static function filterRules(array $rules, array $tabFields): array
    {
        if ($tabFields === []) {
            return [];
        }

        return array_filter(
            $rules,
            fn (string $ruleKey) => static::fieldMatchesTab($ruleKey, $tabFields),
            ARRAY_FILTER_USE_KEY
        );
    }


    public static function filterRequestData(Request $request, array $tabFields): array
    {
        $data = [];

        foreach ($tabFields as $field) {
            if (str_starts_with($field, 'translatable.')) {
                $key = substr($field, strlen('translatable.'));

                if ($request->has("translatable.{$key}")) {
                    $data['translatable'][$key] = $request->input("translatable.{$key}");
                }

                continue;
            }

            if (str_ends_with($field, '.*')) {
                $prefix = substr($field, 0, -2);

                if ($request->has($prefix)) {
                    $data[$prefix] = $request->input($prefix);
                }

                continue;
            }

            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }

        return $data;
    }


    private static function fieldMatchesTab(string $ruleKey, array $tabFields): bool
    {
        foreach ($tabFields as $field) {
            if ($field === $ruleKey) {
                return true;
            }

            if (str_starts_with($field, 'translatable.') && str_starts_with($ruleKey, 'translatable.')) {
                if ($field === $ruleKey) {
                    return true;
                }
            }

            if (str_ends_with($field, '.*')) {
                $prefix = substr($field, 0, -2);

                if ($ruleKey === $prefix || str_starts_with($ruleKey, "{$prefix}.")) {
                    return true;
                }
            }
        }

        return false;
    }
}
