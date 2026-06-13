<?php

namespace Modules\User\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\User\Entities\Role;
use Modules\User\Repositories\Permission;

class UserTabs extends Tabs
{
    public function make()
    {
        $this->group('user_information', trans('user::users.tabs.group.user_information'))
            ->active()
            ->add($this->account())
            ->add($this->permissions())
            ->add($this->newPassword());
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
