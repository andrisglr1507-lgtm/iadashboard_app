@php
    $isActive = false;
    $hasActiveChild = false;
    
    // Check if the current route matches the item's route
    if (isset($item['route']) && request()->routeIs($item['route'])) {
        $isActive = true;
    }
    
    // Recursive function to check if any child/descendant route is active
    $checkActiveChild = function($menuItem) use (&$checkActiveChild) {
        if (isset($menuItem['route']) && request()->routeIs($menuItem['route'])) {
            return true;
        }
        if (isset($menuItem['submenu'])) {
            foreach ($menuItem['submenu'] as $subItem) {
                if ($checkActiveChild($subItem)) {
                    return true;
                }
            }
        }
        return false;
    };

    if (isset($item['submenu'])) {
        foreach ($item['submenu'] as $subItem) {
            if ($checkActiveChild($subItem)) {
                $hasActiveChild = true;
                break;
            }
        }
    }
@endphp

@if(isset($item['submenu']) && count($item['submenu']) > 0)
    <li class="menu-item has-sub">
        <div class="menu-link @if($hasActiveChild) active-sub @endif" data-tooltip="{{ $item['title'] }}">
            <i class="{{ $item['icon'] }}"></i>
            <span class="menu-text">{{ $item['title'] }}</span>
            <i class="fas fa-chevron-right submenu-arrow"></i>
        </div>
        <ul class="submenu @if($hasActiveChild) open @endif">
            @foreach($item['submenu'] as $subItem)
                @include('partials.menu-item', ['item' => $subItem])
            @endforeach
        </ul>
    </li>
@else
    <li class="menu-item">
        <a href="{{ isset($item['route']) ? route($item['route']) : '#' }}" 
           class="menu-link @if($isActive) active-page @endif" 
           data-tooltip="{{ $item['title'] }}">
            <i class="{{ $item['icon'] }}"></i>
            <span class="menu-text">{{ $item['title'] }}</span>
        </a>
    </li>
@endif
