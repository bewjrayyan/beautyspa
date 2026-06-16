<div class="st-tab">
    @if (! empty($lead))
        <p class="st-tab__lead">{{ $lead }}</p>
    @endif

    <div class="st-tab__body">
        {!! $content !!}
    </div>
</div>
