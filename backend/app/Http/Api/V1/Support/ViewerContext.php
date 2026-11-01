<?php

namespace App\Http\Api\V1\Support;

use App\Domain\Identity\Models\User;
use Illuminate\Http\Request;

/** Per-request viewer facts shared by resources (computed once, cached on the request). */
final class ViewerContext
{
    public static function isStaff(Request $request): bool
    {
        if (! $request->attributes->has('viewer.is_staff')) {
            $user = $request->user();
            $request->attributes->set('viewer.is_staff', $user instanceof User && ($user->is_super_admin || $user->isStaffIn()));
        }

        return (bool) $request->attributes->get('viewer.is_staff');
    }

    /** @param list<string> $ids */
    public static function setFavoriteFoodIds(Request $request, array $ids): void
    {
        $request->attributes->set('viewer.favorite_food_ids', array_values($ids));
    }

    /** @return list<string> */
    public static function favoriteFoodIds(Request $request): array
    {
        return $request->attributes->get('viewer.favorite_food_ids', []);
    }
}
