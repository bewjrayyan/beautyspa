@php
    $installSteps = [
        ['key' => 'welcome', 'number' => 1],
        ['key' => 'requirements', 'number' => 2],
        ['key' => 'permissions', 'number' => 3],
        ['key' => 'configuration', 'number' => 4],
        ['key' => 'complete', 'number' => 5],
    ];
@endphp

<ul class="step-list list-inline">
    @foreach ($installSteps as $installStep)
        <li
            class="step-list-item d-flex position-relative"
            @if ($installStep['number'] === 1)
                :class="{ 'active': step === 1, 'complete': step > 1 || appInstalled }"
            @elseif ($installStep['number'] === 2)
                :class="{ 'active': step === 2, 'complete': step > 2 || appInstalled }"
            @elseif ($installStep['number'] === 3)
                :class="{ 'active': step === 3, 'complete': step > 3 || appInstalled }"
            @elseif ($installStep['number'] === 4)
                :class="{ 'active': step === 4 && !appInstalled, 'complete': appInstalled }"
            @else
                :class="{ 'complete': appInstalled }"
            @endif
        >
            <div class="icon d-flex justify-content-center align-items-center rounded-circle">
                @if ($installStep['number'] === 5)
                    <template x-if="appInstalled">
                        @include('install.partials.check-icon')
                    </template>
                    <template x-if="!appInstalled">
                        @include('install.partials.circle-icon')
                    </template>
                @elseif ($installStep['number'] === 4)
                    <template x-if="appInstalled">
                        @include('install.partials.check-icon')
                    </template>
                    <template x-if="!appInstalled && step > 4">
                        @include('install.partials.check-icon')
                    </template>
                    <template x-if="!appInstalled && step <= 4">
                        @include('install.partials.circle-icon')
                    </template>
                @else
                    <template x-if="step > {{ $installStep['number'] }} || appInstalled">
                        @include('install.partials.check-icon')
                    </template>
                    <template x-if="!(step > {{ $installStep['number'] }} || appInstalled)">
                        @include('install.partials.circle-icon')
                    </template>
                @endif
            </div>

            <div>
                <label class="title">{{ trans('install.steps.'.$installStep['key'].'.title') }}</label>
                <span class="excerpt d-block">{{ trans('install.steps.'.$installStep['key'].'.excerpt') }}</span>
            </div>
        </li>
    @endforeach
</ul>
