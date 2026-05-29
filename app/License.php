<?php

namespace AestheticCart;

class License
{
    public function shouldRecheck(): bool
    {
        return false;
    }

    public function valid(): bool
    {
        return true;
    }

    public function shouldCreateLicense(): bool
    {
        return false;
    }

    public function recheck(): void
    {
    }

    public function activate($purchaseCode): void
    {
    }
}
