<?php

namespace Modules\User\Admin;

use Illuminate\Support\Carbon;
use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;

class UserTable extends AdminTable
{
    /**
     * Users use Sentinel activation, not an is_active column.
     *
     * @var bool
     */
    protected bool $editDefaultStatusColumn = false;

    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['user', 'roles', 'status', 'last_login'];


    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('user', function ($user) {
                $name = e($user->full_name);
                $email = e($user->email);
                $avatarUrl = $user->avatarUrl();

                if ($avatarUrl) {
                    $avatar = '<span class="admin-users-table__avatar admin-users-table__avatar--photo">'
                        . '<img src="' . e($avatarUrl) . '" alt="">'
                        . '</span>';
                } else {
                    $avatar = '<span class="admin-users-table__avatar" style="background-color: '
                        . e($user->avatarBackgroundColor())
                        . ';">' . e($user->avatarInitial()) . '</span>';
                }

                return '<div class="admin-users-table__user">'
                    . $avatar
                    . '<span class="admin-users-table__identity">'
                    . '<strong class="admin-users-table__name">' . $name . '</strong>'
                    . '<small class="admin-users-table__email">' . $email . '</small>'
                    . '</span></div>';
            })
            ->addColumn('roles', function ($user) {
                if ($user->roles->isEmpty()) {
                    return '<span class="admin-users-table__muted">—</span>';
                }

                $chips = $user->roles
                    ->map(fn ($role) => '<span class="admin-users-table__role">' . e($role->name) . '</span>')
                    ->implode('');

                return '<div class="admin-users-table__roles">' . $chips . '</div>';
            })
            ->addColumn('status', function ($user) {
                if ($user->isActivated()) {
                    return '<span class="admin-users-table__status admin-users-table__status--active">'
                        . '<i class="fa fa-check-circle" aria-hidden="true"></i> '
                        . e(trans('user::users.index.status_active'))
                        . '</span>';
                }

                return '<span class="admin-users-table__status admin-users-table__status--inactive">'
                    . '<i class="fa fa-minus-circle" aria-hidden="true"></i> '
                    . e(trans('user::users.index.status_inactive'))
                    . '</span>';
            })
            ->editColumn('last_login', function ($user) {
                $lastLogin = $user->last_login;

                if (is_string($lastLogin)) {
                    $lastLogin = Carbon::parse($lastLogin);
                }

                if (! $lastLogin) {
                    return '<span class="admin-users-table__muted">'
                        . e(trans('user::users.profile_page.never_logged_in'))
                        . '</span>';
                }

                return view('admin::partials.table.date')->with('date', $lastLogin);
            })
            ->rawColumns(array_merge($this->rawColumns, $this->defaultRawColumns));
    }
}
