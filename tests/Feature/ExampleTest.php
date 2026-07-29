<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function composer_autoloads_application_classes(): void
    {
        $this->assertTrue(class_exists(\Modules\Order\Entities\Order::class));
    }
}
