<?php

namespace Tests\Feature;

use Composer\InstalledVersions;
use Illuminate\Foundation\Application;
use Modules\Category\Entities\Category;
use Modules\Menu\Entities\MenuItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypiCMS\NestableCollection;

class Laravel12CompatibilityTest extends TestCase
{
    #[Test]
    public function the_application_boots_on_the_patched_laravel_12_release(): void
    {
        $this->assertSame('12', explode('.', Application::VERSION, 2)[0]);
        $this->assertTrue(version_compare(Application::VERSION, '12.61.1', '>='));
    }

    #[Test]
    public function upgraded_authentication_and_datatable_packages_are_installed(): void
    {
        $this->assertTrue(version_compare(
            InstalledVersions::getVersion('cartalyst/sentinel'),
            '9.0.0',
            '>='
        ));
        $this->assertTrue(version_compare(
            InstalledVersions::getVersion('yajra/laravel-datatables-oracle'),
            '12.0.0',
            '>='
        ));
    }

    #[Test]
    public function category_and_menu_models_still_use_nestable_collections(): void
    {
        $this->assertInstanceOf(NestableCollection::class, (new Category())->newCollection());
        $this->assertInstanceOf(NestableCollection::class, (new MenuItem())->newCollection());
    }
}
