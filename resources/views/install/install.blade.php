@extends('install.layout')

@section('content')
    @include('install.welcome')
    @include('install.requirements')
    @include('install.permissions')
    @include('install.configuration')
    @include('install.complete')

    <template x-if="!appInstalled">
        <footer class="footer d-flex justify-content-end">
            <template x-if="isShowPrev">
                <button type="button" class="btn btn-light" :disabled="isPrevDisabled" @click="prevStep">{{ trans('install.buttons.back') }}</button>
            </template>

            <template x-if="!appInstalled">
                <button type="button" class="btn btn-primary" :class="{ 'btn-loading': formSubmitting }" :disabled="isNextDisabled" @click="nextStep">
                    <span x-show="step !== 4">{{ trans('install.buttons.next') }}</span>
                    <span x-show="step === 4">{{ trans('install.buttons.install') }}</span>
                </button>
            </template>
        </footer>
    </template>
@endsection
