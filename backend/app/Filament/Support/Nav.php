<?php

namespace App\Filament\Support;

/** Sidebar group labels shared by the panel provider, resources and pages. */
final class Nav
{
    public static function platform(): string
    {
        return __('Platform');
    }

    public static function content(): string
    {
        return __('Content');
    }

    public static function billing(): string
    {
        return __('Billing');
    }

    public static function operations(): string
    {
        return __('Operations');
    }

    public static function marketing(): string
    {
        return __('Marketing');
    }
}
