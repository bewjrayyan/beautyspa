<template x-if="step === 4 && !appInstalled">
    <div class="has-scrollable-content configuration d-flex flex-column">
        <div class="header overflow-hidden">
            <h3>{{ trans('install.configuration.title') }}</h3>
            <p class="excerpt">{{ trans('install.configuration.excerpt') }}</p>
        </div>

        <div class="content position-relative flex-grow-1 overflow-hidden">
            <div class="scrollable-content" x-ref="configurationContent">
                <template x-if="hasErrorMessage">
                    <div class="alert alert-danger alert-dismissible show fade" :class="{ 'animate__animated animate__headShake': animateAlert }" role="alert">
                        <span x-html="errorMessage"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" @click="resetErrorMessage"></button>
                    </div>
                </template>

                <form class="configuration-form form-horizontal" @input="errors.clear($event.target.name)" x-ref="configurationForm">
                    <div class="box overflow-hidden">
                        <div class="title"><h5>{{ trans('install.configuration.site') }}</h5></div>
                        <div class="row form-group">
                            <label for="app_url" class="col-md-3 col-form-label">URL <span class="required">*</span></label>
                            <div class="col-md-9">
                                <input type="url" autocomplete="off" name="app_url" id="app_url" class="form-control" x-model="form.app_url">
                                <span class="text-muted">{{ trans('install.configuration.app_url_help') }}</span>
                                <template x-if="errors.has('app_url')"><span class="invalid-feedback d-block" x-text="errors.get('app_url')"></span></template>
                            </div>
                        </div>
                    </div>

                    <div class="box overflow-hidden">
                        <div class="title"><h5>{{ trans('install.configuration.database') }}</h5></div>
                        @foreach ([
                            'db_host' => 'localhost',
                            'db_port' => '3306',
                            'db_username' => '',
                            'db_password' => '',
                            'db_database' => '',
                        ] as $field => $default)
                            <div class="row form-group">
                                <label for="{{ $field }}" class="col-md-3 col-form-label">{{ trans('install.attributes.'.$field) }} @if($field !== 'db_password')<span class="required">*</span>@endif</label>
                                <div class="col-md-9">
                                    <input type="{{ $field === 'db_password' ? 'password' : 'text' }}" autocomplete="off" name="{{ $field }}" id="{{ $field }}" class="form-control" x-model="form.{{ $field }}" @if($default && $field !== 'db_password') placeholder="{{ $default }}" @endif>
                                    <template x-if="errors.has('{{ $field }}')"><span class="invalid-feedback" x-text="errors.get('{{ $field }}')"></span></template>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="box overflow-hidden">
                        <div class="title"><h5>{{ trans('install.configuration.admin') }}</h5></div>
                        @foreach (['admin_first_name', 'admin_last_name', 'admin_email', 'admin_phone', 'admin_password', 'admin_password_confirmation'] as $field)
                            <div class="row form-group">
                                <label for="{{ $field }}" class="col-md-3 col-form-label">{{ trans('install.attributes.'.$field) }} <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="{{ str_contains($field, 'password') ? 'password' : ($field === 'admin_email' ? 'email' : ($field === 'admin_phone' ? 'tel' : 'text')) }}" autocomplete="off" name="{{ $field }}" id="{{ $field }}" class="form-control {{ $field === 'admin_phone' ? 'modern-phone-input' : '' }}" x-model="form.{{ $field }}" @if($field === 'admin_phone') @phone:change="form.admin_phone = $event.detail.number" @endif>
                                    <template x-if="errors.has('{{ $field }}')"><span class="invalid-feedback" x-text="errors.get('{{ $field }}')"></span></template>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="box overflow-hidden">
                        <div class="title"><h5>{{ trans('install.configuration.store') }}</h5></div>
                        @foreach (['store_name', 'store_email', 'store_phone'] as $field)
                            <div class="row form-group">
                                <label for="{{ $field }}" class="col-md-3 col-form-label">{{ trans('install.attributes.'.$field) }} <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="{{ $field === 'store_email' ? 'email' : ($field === 'store_phone' ? 'tel' : 'text') }}" autocomplete="off" name="{{ $field }}" id="{{ $field }}" class="form-control {{ $field === 'store_phone' ? 'modern-phone-input' : '' }}" x-model="form.{{ $field }}" @if($field === 'store_phone') @phone:change="form.store_phone = $event.detail.number" @endif>
                                    <template x-if="errors.has('{{ $field }}')"><span class="invalid-feedback" x-text="errors.get('{{ $field }}')"></span></template>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
