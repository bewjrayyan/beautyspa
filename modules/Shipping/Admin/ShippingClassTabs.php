<?php

namespace Modules\Shipping\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class ShippingClassTabs extends Tabs
{
    public function make()
    {
        $this->group('shipping_class_information', trans('shipping::shipping_classes.tabs.group.shipping_class_information'))
            ->active()
            ->add($this->general());
    }


    private function general()
    {
        return tap(new Tab('general', trans('shipping::shipping_classes.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'cost', 'is_active']);
            $tab->view('shipping::admin.shipping_classes.tabs.general');
        });
    }
}
