<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Foundation\Console\OptimizeCommand as BaseOptimizeCommand;
use Illuminate\Support\ServiceProvider;

class OptimizeCommand extends BaseOptimizeCommand
{
    protected function getOptimizeTasks()
    {
        return [
            'config' => 'config:cache',
            'events' => 'event:cache',
            'views' => 'view:cache',
            ...ServiceProvider::$optimizeCommands,
        ];
    }
}
