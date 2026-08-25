@extends('storefront::public.layout')

@section('title', trans('account::consultation.access.title'))

@section('content')
<section class="consultation-access-wrap">
    <div class="container">
        <div class="consultation-access-card">
            <div class="consultation-access-card__icon"><i class="las la-user-shield"></i></div>
            <span class="consultation-access-card__treatment">{{ $submission->form_title }}</span>
            <h1>{{ trans('account::consultation.access.title') }}</h1>
            <p>{{ trans('account::consultation.access.lead') }}</p>
            @include('storefront::public.auth.partials.notification')
            <form method="POST" action="{{ route('consultations.lookup', ['token' => $token]) }}">
                @csrf
                <label for="consultation-identifier">{{ trans('account::consultation.access.identifier') }}</label>
                <input id="consultation-identifier" class="form-control" name="identifier" value="{{ old('identifier') }}" placeholder="{{ trans('account::consultation.access.placeholder') }}" required autofocus>
                {!! $errors->first('identifier', '<span class="help-block text-red">:message</span>') !!}
                <button class="btn btn-primary btn-block" type="submit">{{ trans('account::consultation.access.continue') }}</button>
            </form>
            <div class="consultation-access-card__privacy"><i class="las la-lock"></i><span>{{ trans('account::consultation.access.privacy') }}</span></div>
        </div>
    </div>
</section>
<style>
.consultation-access-wrap{min-height:70vh;display:grid;place-items:center;padding:60px 0;background:radial-gradient(circle at 20% 20%,rgba(181,8,99,.1),transparent 35%),#faf8f9}.consultation-access-card{max-width:560px;margin:auto;padding:42px;border:1px solid #eee2e9;border-radius:24px;background:#fff;box-shadow:0 24px 70px rgba(71,32,55,.1)}.consultation-access-card__icon{display:grid;place-items:center;width:58px;height:58px;margin-bottom:20px;border-radius:18px;color:#fff;background:linear-gradient(145deg,#b50863,#79033f);font-size:28px}.consultation-access-card__treatment{display:inline-block;margin-bottom:8px;padding:5px 10px;border-radius:999px;color:#9b0755;background:#fce8f3;font-size:12px;font-weight:700}.consultation-access-card h1{margin:0 0 10px;font-size:30px}.consultation-access-card>p{margin-bottom:25px;color:#756b72}.consultation-access-card label{display:block;margin-bottom:8px;font-weight:700}.consultation-access-card .form-control{height:50px;margin-bottom:8px;border-radius:12px}.consultation-access-card .btn{min-height:50px;margin-top:16px;border-radius:12px}.consultation-access-card__privacy{display:flex;gap:10px;margin-top:22px;padding-top:20px;border-top:1px solid #eee6eb;color:#82767e;font-size:12px;line-height:1.5}@media(max-width:600px){.consultation-access-wrap{padding:25px 0}.consultation-access-card{padding:28px 22px;border-radius:18px}}
</style>
@endsection
