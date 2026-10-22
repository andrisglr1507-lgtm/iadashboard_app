@props([
    'title' => 'Belum Ada Data',
    'description' => 'Tidak ada data yang dapat ditampilkan saat ini.',
    'image' => asset('images/empty-box.png'),
    'imageWidth' => '130px',
    'actionUrl' => null,
    'actionText' => null,
    'actionIcon' => null,
    'actionOnClick' => null,
    'colspan' => null,
    'compact' => false,
])

@php
    $wrapperClass = 'empty-state-box' . ($compact ? ' empty-state-compact' : '');
@endphp

@if($colspan)
<tr class="empty-state-row">
    <td colspan="{{ $colspan }}" class="empty-state-cell">
@endif

<div class="{{ $wrapperClass }}">
    <div class="empty-state-illustration">
        <div class="empty-state-aura"></div>
        <img 
            src="{{ $image }}" 
            alt="Empty State" 
            class="empty-state-img"
            style="width: {{ $imageWidth }};"
            loading="lazy"
        />
    </div>

    @if($title)
        <h4 class="empty-state-title">{{ $title }}</h4>
    @endif

    @if($description)
        <p class="empty-state-description">{{ $description }}</p>
    @endif

    {{-- Action Button / Slot --}}
    @if($actionUrl || $actionOnClick || $actionText || !empty(trim($slot)))
        <div class="empty-state-actions">
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="empty-state-btn" @if($actionOnClick) onclick="{{ $actionOnClick }}" @endif>
                    @if($actionIcon)
                        <i class="{{ $actionIcon }}"></i>
                    @endif
                    <span>{{ $actionText ?? 'Aksi' }}</span>
                </a>
            @elseif($actionOnClick)
                <button type="button" class="empty-state-btn" onclick="{{ $actionOnClick }}">
                    @if($actionIcon)
                        <i class="{{ $actionIcon }}"></i>
                    @endif
                    <span>{{ $actionText ?? 'Aksi' }}</span>
                </button>
            @endif

            {{ $slot }}
        </div>
    @endif
</div>

@if($colspan)
    </td>
</tr>
@endif
