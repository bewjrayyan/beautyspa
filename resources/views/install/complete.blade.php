<template x-if="appInstalled">
    <div class="complete d-flex flex-column justify-content-center align-items-center">
        <div class="check-icon">
            <span class="icon-line line-tip"></span>
            <span class="icon-line line-long"></span>
            <div class="icon-circle"></div>
            <div class="icon-fix"></div>
        </div>

        <h3 class="title text-center animate__animated animate__fadeInUp">{{ trans('install.complete.title') }}</h3>

        <div class="box install-complete-checklist animate__animated animate__fadeInUp">
            <h5>{{ trans('install.complete.checklist_title') }}</h5>
            <ul class="install-checklist">
                @foreach (trans('install.complete.checklist') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>

        <ul class="links list-inline d-flex animate__animated animate__fadeInUp">
            <li>
                <a href="{{ url('admin') }}" target="_blank" class="link text-center">
                    <span class="d-block"><b>{{ trans('install.complete.admin_panel') }}</b></span>
                </a>
            </li>
            <li>
                <a href="{{ url('/') }}" target="_blank" class="link text-center">
                    <span class="d-block"><b>{{ trans('install.complete.storefront') }}</b></span>
                </a>
            </li>
        </ul>
    </div>
</template>
