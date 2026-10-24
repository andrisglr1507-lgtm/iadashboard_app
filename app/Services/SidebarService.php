<?php

namespace App\Services;

use App\Models\User;

class SidebarService
{
    /**
     * Filter sidebar items for the authenticated user.
     *
     * @param array $items
     * @param User|null $user
     * @return array
     */
    public static function filter(array $items, ?User $user = null): array
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return [];
        }

        $filtered = [];
        $currentHeader = null;
        $itemsInCurrentHeader = [];

        foreach ($items as $item) {
            if (isset($item['header'])) {
                // If previous header had visible items, push header + items
                if ($currentHeader !== null && !empty($itemsInCurrentHeader)) {
                    $filtered[] = $currentHeader;
                    $filtered = array_merge($filtered, $itemsInCurrentHeader);
                }

                // Start new header section
                $currentHeader = $item;
                $itemsInCurrentHeader = [];
                continue;
            }

            // Normal menu item or parent item
            $processedItem = self::filterItem($item, $user);
            if ($processedItem !== null) {
                if ($currentHeader !== null) {
                    $itemsInCurrentHeader[] = $processedItem;
                } else {
                    $filtered[] = $processedItem;
                }
            }
        }

        // Flush last header section
        if ($currentHeader !== null && !empty($itemsInCurrentHeader)) {
            $filtered[] = $currentHeader;
            $filtered = array_merge($filtered, $itemsInCurrentHeader);
        }

        return $filtered;
    }

    /**
     * Filter a single item recursively.
     *
     * @param array $item
     * @param User $user
     * @return array|null
     */
    public static function filterItem(array $item, User $user): ?array
    {
        // 1. Check roles restriction if specified
        if (isset($item['roles'])) {
            $allowedRoles = (array) $item['roles'];
            if (!$user->hasRole($allowedRoles)) {
                return null;
            }
        }

        // 2. If it has a submenu, filter each child
        if (isset($item['submenu']) && is_array($item['submenu'])) {
            $visibleSubmenu = [];
            foreach ($item['submenu'] as $subItem) {
                $filteredSub = self::filterItem($subItem, $user);
                if ($filteredSub !== null) {
                    $visibleSubmenu[] = $filteredSub;
                }
            }

            // If a parent item has no visible children left, hide the parent item
            if (empty($visibleSubmenu)) {
                return null;
            }

            $item['submenu'] = $visibleSubmenu;
        }

        return $item;
    }
}
