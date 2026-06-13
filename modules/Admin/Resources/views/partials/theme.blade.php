@php
    $adminSidebarBg = setting('admin_sidebar_color') ?: '#222530';
    $adminSidebarAccent = setting('admin_sidebar_accent_color') ?: '#475aff';
@endphp

<style>
    :root {
        --admin-sidebar-bg: {{ $adminSidebarBg }};
        --admin-sidebar-accent: {{ $adminSidebarAccent }};
    }
</style>
