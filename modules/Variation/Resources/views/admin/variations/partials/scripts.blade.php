@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('admin::admin.shortcuts.back_to_index', ['name' => trans('variation::variations.variation')]) }}</dd>
    </dl>
@endpush

@push('globals')
    <script>
        AestheticCart.langs['variation::variations.group.general'] = '{{ trans('variation::variations.group.general') }}';
        AestheticCart.langs['variation::attributes.name'] = '{{ trans('variation::attributes.name') }}';
        AestheticCart.langs['variation::attributes.type'] = '{{ trans('variation::attributes.type') }}';
        AestheticCart.langs['variation::variations.form.variation_types.please_select'] = '{{ trans('variation::variations.form.variation_types.please_select') }}';
        AestheticCart.langs['variation::variations.form.variation_types.text'] = '{{ trans('variation::variations.form.variation_types.text') }}';
        AestheticCart.langs['variation::variations.form.variation_types.color'] = '{{ trans('variation::variations.form.variation_types.color') }}';
        AestheticCart.langs['variation::variations.form.variation_types.image'] = '{{ trans('variation::variations.form.variation_types.image') }}';
        AestheticCart.langs['variation::variations.group.values'] = '{{ trans('variation::variations.group.values') }}';
        AestheticCart.langs['variation::variations.form.label'] = '{{ trans('variation::variations.form.label') }}';
        AestheticCart.langs['variation::variations.form.color'] = '{{ trans('variation::variations.form.color') }}';
        AestheticCart.langs['variation::variations.form.image'] = '{{ trans('variation::variations.form.image') }}';
        AestheticCart.langs['variation::variations.form.add_row'] = '{{ trans('variation::variations.form.add_row') }}';
        AestheticCart.langs['admin::admin.buttons.save'] = '{{ trans('admin::admin.buttons.save') }}';
    </script>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([{
            key: 'b',
            route: "{{ route('admin.variations.index') }}"
        }, ]);
    </script>
@endpush
