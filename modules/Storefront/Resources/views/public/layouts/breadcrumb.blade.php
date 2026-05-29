@hasSection('breadcrumb')
    <div class="container">
        <div class="breadcrumb">
            <ul class="list-inline">
                <li>
                    <a href="{{ storefront_home_url() }}">{{ trans('storefront::layouts.home') }}</a>
                </li>

                @yield('breadcrumb')
            </ul>
        </div>
    </div>
@endif
