@hasAccess('admin.media.index')
    <div class="st-banner-block">
        <div class="panel-image image-holder st-banner-block__image">
            @if (is_null($banner->image->path))
                <i class="fa fa-picture-o" aria-hidden="true"></i>
                <img class="hide" alt="">
            @else
                <img src="{{ $banner->image->path }}" alt="Banner">
                <button type="button" class="btn remove-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M6.00098 17.9995L17.9999 6.00053" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17.9999 17.9995L6.00098 6.00055" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            @endif

            <input type="hidden" name="translatable[{{ $name }}_file_id]" value="{{ $banner->image->id }}" class="banner-file-id">
        </div>

        <div class="st-banner-block__fields">
            <div class="form-group">
                <label for="{{ $name }}-call-to-action-url" class="control-label">
                    {{ trans('storefront::attributes.call_to_action_url') }}
                </label>
                <input
                    type="text"
                    name="{{ $name }}_call_to_action_url"
                    value="{{ old($name . '_call_to_action_url', $banner->call_to_action_url) }}"
                    class="form-control"
                    id="{{ $name }}-call-to-action-url"
                >
            </div>

            <div class="checkbox">
                <input type="hidden" name="{{ $name }}_open_in_new_window" value="0">
                <input type="checkbox" name="{{ $name }}_open_in_new_window" value="1" id="{{ $name }}-open-in-new-window" {{ $banner->open_in_new_window ? 'checked' : '' }}>
                <label for="{{ $name }}-open-in-new-window">
                    {{ trans('storefront::attributes.open_in_new_window') }}
                </label>
            </div>
        </div>
    </div>
@endHasAccess
