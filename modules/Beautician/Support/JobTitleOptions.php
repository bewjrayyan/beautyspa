<?php

namespace Modules\Beautician\Support;

class JobTitleOptions
{
    public static function forSelect(?string $current = null): array
    {
        $options = [
            '' => trans('beautician::beauticians.form.job_title_choose'),
        ];

        foreach (trans('beautician::beauticians.job_titles') as $title) {
            $options[$title] = $title;
        }

        if ($current !== null && $current !== '' && ! array_key_exists($current, $options)) {
            $options[$current] = $current;
        }

        return $options;
    }
}
