<?php

namespace App\Domain\Tenancy\Services;

/** Single source of truth for roles → permissions (seeded into spatie/permission). */
final class Roles
{
    public const PERMISSIONS = [
        'clients.view', 'clients.manage', 'clients.invite',
        'programs.view', 'programs.manage', 'exercises.manage',
        'nutrition.manage', 'habits.manage', 'checkins.review',
        'messages.send', 'messages.broadcast', 'automations.manage',
        'community.moderate', 'calendar.manage', 'bookings.manage',
        'payments.view', 'payments.manage', 'products.manage',
        'analytics.view', 'crm.manage', 'team.manage', 'settings.manage', 'billing.manage',
        'ai.use', 'integrations.manage',
        // client-side
        'self.log', 'self.book', 'self.purchase', 'community.post',
    ];

    /** @return array<string, list<string>> */
    public static function map(): array
    {
        $staffCore = [
            'clients.view', 'clients.manage', 'clients.invite', 'programs.view', 'programs.manage', 'exercises.manage',
            'nutrition.manage', 'habits.manage', 'checkins.review', 'messages.send', 'messages.broadcast',
            'automations.manage', 'community.moderate', 'calendar.manage', 'bookings.manage', 'payments.view',
            'crm.manage', 'ai.use', 'community.post',
        ];

        return [
            'owner' => self::PERMISSIONS,
            'coach' => [...$staffCore, 'products.manage', 'analytics.view', 'integrations.manage'],
            'assistant' => ['clients.view', 'programs.view', 'checkins.review', 'messages.send', 'calendar.manage', 'bookings.manage', 'community.moderate', 'community.post'],
            'client' => ['self.log', 'self.book', 'self.purchase', 'community.post'],
        ];
    }
}
