<aside class="admin-profile-create-sidebar">
    <div class="admin-profile-create-sidebar__head">
        <h3>
            <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
            {{ trans('user::users.create_page.sidebar_title') }}
        </h3>
        <p>{{ trans('user::users.create_page.sidebar_lead') }}</p>
    </div>

    <ul class="admin-profile-create-sidebar__tips">
        <li>
            <i class="fa fa-shield" aria-hidden="true"></i>
            <span>{{ trans('user::users.create_page.tip_roles') }}</span>
        </li>
        <li>
            <i class="fa fa-lock" aria-hidden="true"></i>
            <span>{{ trans('user::users.create_page.tip_password') }}</span>
        </li>
        <li>
            <i class="fa fa-check-circle" aria-hidden="true"></i>
            <span>{{ trans('user::users.create_page.tip_activate') }}</span>
        </li>
        <li>
            <i class="fa fa-key" aria-hidden="true"></i>
            <span>{{ trans('user::users.create_page.tip_permissions') }}</span>
        </li>
    </ul>
</aside>
