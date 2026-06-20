<button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('admin::admin.buttons.close') }}">
    @if (! empty($times))
        <span aria-hidden="true">&times;</span>
    @else
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5.00082 14.9995L14.9999 5.00041" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14.9999 14.9996L5.00082 5.00049" stroke="#555555" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    @endif
</button>
