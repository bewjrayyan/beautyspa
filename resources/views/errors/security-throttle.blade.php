@extends('errors.layout')

@section('code', '429')
@section('eyebrow', trans('errors.security_throttle.eyebrow'))
@section('title', trans('errors.security_throttle.title'))
@section('message', trans('errors.security_throttle.message'))

@section('guidance')
    <div class="error-guidance" role="status">
        <span class="error-guidance__icon" aria-hidden="true">✓</span>
        <div>
            <strong>{{ trans('errors.security_throttle.guidance_title') }}</strong>
            <p>{{ trans('errors.security_throttle.guidance') }}</p>
        </div>
    </div>
@endsection

@section('support', trans('errors.security_throttle.support'))
