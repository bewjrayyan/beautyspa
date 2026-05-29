<?php

namespace Modules\User\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class ProfileTabs extends Tabs
{
    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    public function make()
    {
        $this->group('profile_information', trans('user::users.tabs.group.profile_information'))
            ->active()
            ->add($this->account())
            ->add($this->newPassword());
    }


    private function account()
    {
        return tap(new Tab('account', trans('user::users.tabs.account')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields([
                'first_name',
                'last_name',
                'identity_number',
                'date_of_birth',
                'email',
                'phone',
                'address_1',
                'address_2',
                'city',
                'state',
                'zip',
                'country',
                'avatar',
            ]);
            $tab->view('user::admin.profile.tabs.account');
        });
    }


    private function newPassword()
    {
        return tap(new Tab('newPassword', trans('user::users.tabs.new_password')), function (Tab $tab) {
            $tab->weight(10);
            $tab->fields(['password', 'password_confirmation']);
            $tab->view('user::admin.profile.tabs.new_password');
        });
    }


    public function renderProfile(array $data = [])
    {
        $this->activateTabFromRequest();

        return view('user::admin.partials.account-layout', array_merge($data, [
            'tabs' => $this,
            'name' => class_basename($this),
            'groups' => $this->groups(),
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
        ]));
    }
}
