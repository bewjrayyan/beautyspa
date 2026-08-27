<?php

namespace Modules\User\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\User\Entities\Role;
use Modules\User\Repositories\Permission;
use Modules\Account\Services\ConsultationFormService;

class UserTabs extends Tabs
{
    public function make()
    {
        $this->group('user_information', trans('user::users.tabs.group.user_information'))
            ->active()
            ->add($this->account())
            ->add($this->orders())
            ->add($this->consultations())
            ->add($this->permissions())
            ->add($this->newPassword());
    }



    private function orders()
    {
        if (! request()->routeIs('admin.users.edit')) {
            return;
        }

        if (! app('modules')->isEnabled('Order')) {
            return;
        }

        return tap(new Tab('orders', trans('user::users.tabs.orders')), function (Tab $tab) {
            $tab->weight(12);
            $tab->view(function ($data) {
                $user = $data['user'];
                $orders = $user->orders()
                    ->with(['beautician', 'spaBranch'])
                    ->latest()
                    ->paginate(15, ['*'], 'orders_page')
                    ->appends(array_merge(request()->except('orders_page'), ['tab' => 'orders']));

                return view('user::admin.users.tabs.orders', [
                    'user' => $user,
                    'orders' => $orders,
                ]);
            });
        });
    }


    private function consultations()
    {
        if (! request()->routeIs('admin.users.edit')) {
            return;
        }

        return tap(new Tab('consultations', trans('account::consultation.admin.tab')), function (Tab $tab) {
            $tab->weight(15);
            $tab->view(function ($data) {
                $user = $data['user'];
                $forms = app(ConsultationFormService::class);

                return view('user::admin.users.tabs.consultations', [
                    'user' => $user,
                    'pendingForms' => $forms->pendingFor($user),
                    'submissions' => $forms->historyFor($user),
                ]);
            });
        });
    }


    private function account()
    {
        return tap(new Tab('account', trans('user::users.tabs.account')), function (Tab $tab) {
            $tab->active();
            $tab->weight(10);

            $fields = [
                'first_name',
                'last_name',
                'identity_number',
                'date_of_birth',
                'email',
                'phone',
                'avatar',
                'activated',
                'roles',
            ];

            if (! request()->routeIs('admin.users.create')) {
                $fields = array_merge($fields, [
                    'address_1',
                    'address_2',
                    'city',
                    'state',
                    'zip',
                    'country',
                ]);
            }

            if (request()->routeIs('admin.users.create')) {
                $fields[] = 'password';
                $fields[] = 'password_confirmation';
            }

            $tab->fields($fields);

            $tab->view('user::admin.users.tabs.account');
        });
    }


    private function permissions()
    {
        return tap(new Tab('permissions', trans('user::users.tabs.permissions')), function (Tab $tab) {
            $tab->weight(20);

            $tab->view(function ($data) {
                return view('user::admin.partials.permissions.index', [
                    'entity' => $data['user'],
                    'permissions' => Permission::all(),
                ]);
            });
        });
    }


    private function newPassword()
    {
        if (!request()->routeIs('admin.users.edit')) {
            return;
        }

        return tap(new Tab('new_password', trans('user::users.tabs.new_password')), function (Tab $tab) {
            $tab->weight(30);
            $tab->fields(['password', 'password_confirmation']);
            $tab->view('user::admin.users.tabs.new_password');
        });
    }


    public function renderAccountLayout(array $data = [])
    {
        $this->activateTabFromRequest();

        return view('user::admin.partials.account-layout', array_merge($data, [
            'tabs' => $this,
            'name' => class_basename($this),
            'groups' => $this->groups(),
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
            'profileUser' => $data['profileUser'] ?? $data['user'] ?? null,
        ]));
    }
}
