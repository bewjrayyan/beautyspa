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
    protected array $rawColumns = ['user', 'roles', 'loyalty_member', 'status', 'last_login', 'actions'];


    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('user', function ($user) {
                $avatar = view('user::admin.partials.avatar', [
                    'user' => $user,
                    'class' => 'admin-users-table__avatar',
                ])->render();

                return '<div class="admin-users-table__user">'
                    . $avatar
                    . '<span class="admin-users-table__identity">'
                    . '<strong class="admin-users-table__name">' . e($user->full_name) . '</strong>'
                    . '<small class="admin-users-table__email">' . e($user->email) . '</small>'
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
            ->addColumn('loyalty_member', function ($user) {
                if (! app('modules')->isEnabled('Loyalty')) {
                    return '';
                }

                if (! $user->isCustomer()) {
                    return '<span class="admin-users-table__muted">—</span>';
                }

                $wallet = $user->loyaltyWallet;

                if ($wallet) {
                    $tier = $wallet->tier?->translatedName();
                    $label = e(trans('user::users.index.loyalty_member_yes'));

                    if ($tier) {
                        $label .= ' <span class="admin-users-table__loyalty-tier">' . e($tier) . '</span>';
                    }

                    if (auth()->user()?->hasAccess('admin.loyalty.members.show')) {
                        $url = route('admin.loyalty.members.show', $wallet);

                        return '<a href="' . e($url) . '" class="admin-users-table__loyalty admin-users-table__loyalty--yes" onclick="event.stopPropagation()">'
                            . '<i class="fa fa-star" aria-hidden="true"></i> '
                            . $label
                            . '</a>';
                    }

                    return '<span class="admin-users-table__loyalty admin-users-table__loyalty--yes">'
                        . '<i class="fa fa-star" aria-hidden="true"></i> '
                        . $label
                        . '</span>';
                }

                return '<span class="admin-users-table__loyalty admin-users-table__loyalty--no">'
                    . '<i class="fa fa-circle-o" aria-hidden="true"></i> '
                    . e(trans('user::users.index.loyalty_member_no'))
                    . '</span>';
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
            ->addColumn('actions', function ($user) {
                if (! auth()->user()->hasAccess('admin.users.edit')) {
                    return '';
                }

                $url = route('admin.users.edit', $user);

                return '<a href="' . e($url) . '" class="btn btn-default btn-sm admin-users-table__edit" onclick="event.stopPropagation()">'
                    . '<i class="fa fa-pencil" aria-hidden="true"></i> '
                    . e(trans('user::users.index.edit'))
                    . '</a>';
            })
            ->rawColumns(array_merge($this->rawColumns, $this->defaultRawColumns));
    }
}
