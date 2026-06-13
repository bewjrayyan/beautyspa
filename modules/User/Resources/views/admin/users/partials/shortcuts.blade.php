@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('user::users.navigation.back_to_index') }}</dd>
    </dl>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'b', route: "{{ route('admin.users.index') }}" },
        ]);
    </script>
@endpush
