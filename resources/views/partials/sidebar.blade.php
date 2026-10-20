<aside class="sidebar" id="sidebar" style="display: flex; flex-direction: column; height: 100vh;">
    <script>
        // Prevent flicker by setting state synchronously before DOM paints
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
            document.write('<style>#sidebar, #sidebar * { transition: none !important; }</style>');
            // Hapus style transition disable setelah render selesai
            setTimeout(() => {
                const styleSheets = document.querySelectorAll('style');
                styleSheets.forEach(s => {
                    if(s.innerHTML.includes('transition: none !important;')) s.remove();
                });
            }, 100);
        }
    </script>
    <!-- Brand Logo -->
    <div id="headerBrandContainer" style="display: flex; align-items: center; gap: 12px; height: 70px; padding-left: 18px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; white-space: nowrap;">
        <img src="{{ asset('images/icon_app_ia.png') }}" alt="Logo IA" style="width: 36px; height: 36px; flex-shrink: 0; object-fit: contain; border-radius: 4px;">
        <span id="headerBrandText" style="font-weight: 500; font-size: 1.25rem; color: #ECF0F1; letter-spacing: 0.5px; transition: opacity 0.2s;">Dashboard</span>
    </div>

    <div class="sidebar-menu" style="flex-grow: 1; overflow-y: auto; overflow-x: hidden;">
        <ul class="menu-list" id="menuList">
            @foreach(config('sidebar', []) as $item)
                @include('partials.menu-item', ['item' => $item])
            @endforeach
        </ul>
    </div>
    
    <div class="sidebar-footer" style="padding: 16px;">
        
        <!-- Toggle Collapse Button -->
        <div id="toggleSidebarBtn" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 20px; cursor: pointer; color: #8ba0b5; transition: color 0.2s; padding: 0 4px;" onmouseover="this.style.color='#1ABB9C'" onmouseout="this.style.color='#8ba0b5'">
            <div style="width: 32px; display: flex; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-chevron-left fs-6" id="toggleIcon"></i>
            </div>
            <span class="menu-text" id="toggleText" style="margin-left: 8px; font-weight: 600; font-size: 0.85rem; white-space: nowrap;">Collapse Sidebar</span>
        </div>


        <!-- Account user -->
        @auth
        <div class="dropdown dropup" style="width: 100%;">
            <div data-bs-toggle="dropdown" aria-expanded="false" style="display: flex; align-items: center; justify-content: flex-start; gap: 8px; cursor: pointer; padding: 4px; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                <!-- Avatar -->
                <div style="width: 32px; display: flex; justify-content: center; flex-shrink: 0;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #1ABB9C; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem;">
                        {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->username, 0, 1)) }}
                    </div>
                </div>
                <!-- User Details (Hidden when collapsed) -->
                <div class="header-text" style="display: flex; flex-direction: column; overflow: hidden; flex: 1;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #ECF0F1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->full_name ?? Auth::user()->username }}</span>
                    <!-- <span style="font-size: 0.7rem; color: #8ba0b5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->role ?? 'Administrator' }}</span> -->
                </div>
            </div>
            
            <ul class="dropdown-menu dropdown-menu-dark shadow" style="min-width: 200px; border-radius: 8px; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.1); background-color: var(--phoenix-dark);">
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#" style="gap: 12px; font-size: 0.85rem; color: #ECF0F1;">
                        <i class="fas fa-user text-center" style="width: 16px;"></i> My Profile
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#" style="gap: 12px; font-size: 0.85rem; color: #ECF0F1;">
                        <i class="fas fa-key text-center" style="width: 16px;"></i> Change Password
                    </a>
                </li>
                <li><hr class="dropdown-divider" style="border-color: rgba(255,255,255,0.1);"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item d-flex align-items-center py-2 text-danger" style="gap: 12px; font-size: 0.85rem; background: transparent; border: none; width: 100%; text-align: left;">
                            <i class="fas fa-sign-out-alt text-center" style="width: 16px;"></i> Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endauth
    </div>
</aside>

<!-- Collapsed Sidebar Flyout Menu Card -->
<div id="sidebarFlyout" class="sidebar-flyout" style="display: none;"></div>

<style>
/* Collapsed Sidebar Flyout Card Styles */
.sidebar.collapsed .menu-link:hover::after {
    display: none !important; /* Nonaktifkan tooltip CSS lama agar tidak tabrakan */
}

.sidebar-flyout {
    position: fixed;
    z-index: 999999;
    min-width: 220px;
    max-width: 280px;
    background: #2A3F54;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.5), 0 4px 12px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    opacity: 0;
    transform: translateX(-8px);
    transition: opacity 0.18s cubic-bezier(0.4, 0, 0.2, 1), transform 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none;
}

/* Jembatan tak terlihat agar kursor tidak lepas saat bergeser dari icon ke card */
.sidebar-flyout::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: -16px;
    width: 20px;
    background: transparent;
}

.sidebar-flyout.show {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
}

.sidebar-flyout .flyout-header {
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.32) 0%, rgba(0, 0, 0, 0.15) 100%);
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-flyout .flyout-header .flyout-header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #1ABB9C;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.sidebar-flyout .flyout-header .flyout-header-icon svg {
    width: 20px;
    height: 20px;
    stroke: #1ABB9C;
    fill: none;
}

.sidebar-flyout .flyout-header .flyout-header-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #ECF0F1;
    letter-spacing: 0.4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-flyout .flyout-body {
    padding: 6px 0;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}

.sidebar-flyout .flyout-body::-webkit-scrollbar {
    width: 4px;
}
.sidebar-flyout .flyout-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}

.sidebar-flyout .flyout-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 16px;
    color: #c2cdd8;
    text-decoration: none;
    font-size: 0.825rem;
    font-weight: 500;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 3px solid transparent;
}

.sidebar-flyout .flyout-item:hover {
    background: rgba(26, 187, 156, 0.18);
    color: #ffffff;
    padding-left: 20px;
}

.sidebar-flyout .flyout-item.active {
    background: rgba(26, 187, 156, 0.12);
    color: #1ABB9C;
    font-weight: 700;
    border-left-color: #1ABB9C;
}

.sidebar-flyout .flyout-item-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    font-size: 0.85rem;
    flex-shrink: 0;
    color: inherit;
}

.sidebar-flyout .flyout-item-icon svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    fill: none;
}

.sidebar-flyout .flyout-bullet {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #5c738b;
    display: inline-block;
    transition: all 0.2s;
}

.sidebar-flyout .flyout-item:hover .flyout-bullet {
    background: #1ABB9C;
    transform: scale(1.3);
}

.sidebar-flyout .flyout-item.active .flyout-bullet {
    background: #1ABB9C;
}

.sidebar-flyout .flyout-item-text {
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-flyout .flyout-single-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #ECF0F1;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-flyout .flyout-single-link:hover {
    background: rgba(26, 187, 156, 0.18);
    color: #1ABB9C;
}

.sidebar-flyout .flyout-single-link.active {
    background: rgba(26, 187, 156, 0.15);
    color: #1ABB9C;
    border-left: 3px solid #1ABB9C;
}
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        const flyout = document.getElementById('sidebarFlyout');
        let hideFlyoutTimer = null;
        let activeHoveredItem = null;

        function closeFlyout() {
            if (!flyout) return;
            flyout.classList.remove('show');
            setTimeout(() => {
                if (!flyout.classList.contains('show')) {
                    flyout.style.display = 'none';
                    flyout.innerHTML = '';
                }
            }, 180);
            activeHoveredItem = null;
        }

        function scheduleHideFlyout() {
            clearTimeout(hideFlyoutTimer);
            hideFlyoutTimer = setTimeout(closeFlyout, 160);
        }

        function openFlyoutForItem(menuItem) {
            if (!sidebar || !sidebar.classList.contains('collapsed') || !flyout) return;
            clearTimeout(hideFlyoutTimer);
            activeHoveredItem = menuItem;

            const isHasSub = menuItem.classList.contains('has-sub');
            const mainLink = menuItem.querySelector(':scope > .menu-link');
            if (!mainLink) return;

            const title = mainLink.querySelector('.menu-text')?.textContent.trim() 
                       || mainLink.getAttribute('data-tooltip') 
                       || '';
            
            const iconEl = mainLink.querySelector('i:not(.submenu-arrow), svg');
            const iconHtml = iconEl ? iconEl.outerHTML : '';

            if (isHasSub) {
                const subLinks = menuItem.querySelectorAll(':scope > .submenu > .menu-item > .menu-link');
                if (!subLinks || subLinks.length === 0) return;

                let itemsHtml = '';
                subLinks.forEach(subLink => {
                    const href = subLink.getAttribute('href') || '#';
                    const subTitle = subLink.querySelector('.menu-text')?.textContent.trim() 
                                  || subLink.getAttribute('data-tooltip') 
                                  || '';
                    const isActive = subLink.classList.contains('active-page');
                    const subIconEl = subLink.querySelector('i, svg');
                    const subIconHtml = subIconEl ? subIconEl.outerHTML : '<span class="flyout-bullet"></span>';

                    itemsHtml += `
                        <a href="${href}" class="flyout-item ${isActive ? 'active' : ''}">
                            <span class="flyout-item-icon">${subIconHtml}</span>
                            <span class="flyout-item-text">${subTitle}</span>
                            ${isActive ? '<i class="fas fa-check-circle ms-auto" style="color: #1ABB9C; font-size: 0.75rem;"></i>' : ''}
                        </a>
                    `;
                });

                flyout.innerHTML = `
                    <div class="flyout-header">
                        <span class="flyout-header-icon">${iconHtml}</span>
                        <span class="flyout-header-title">${title}</span>
                    </div>
                    <div class="flyout-body">
                        ${itemsHtml}
                    </div>
                `;
            } else {
                const href = mainLink.getAttribute('href') || '#';
                const isActive = mainLink.classList.contains('active-page');

                flyout.innerHTML = `
                    <a href="${href}" class="flyout-single-link ${isActive ? 'active' : ''}">
                        <span class="flyout-header-icon">${iconHtml}</span>
                        <span class="flyout-header-title">${title}</span>
                        ${isActive ? '<span class="badge ms-auto" style="font-size: 0.65rem; background-color: #1ABB9C !important; color: white;">Aktif</span>' : '<i class="fas fa-chevron-right ms-auto" style="font-size: 0.7rem; opacity: 0.5;"></i>'}
                    </a>
                `;
            }

            // Hitung posisi floating card
            flyout.style.display = 'block';
            flyout.style.visibility = 'hidden';

            const itemRect = menuItem.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();
            const flyoutHeight = flyout.offsetHeight;

            let top = itemRect.top;
            if (top + flyoutHeight > window.innerHeight - 15) {
                top = Math.max(15, window.innerHeight - flyoutHeight - 15);
            }
            if (top < 15) top = 15;

            flyout.style.top = top + 'px';
            flyout.style.left = (sidebarRect.right + 6) + 'px';
            flyout.style.visibility = 'visible';

            // Trigger animasi show
            requestAnimationFrame(() => {
                flyout.classList.add('show');
            });
        }
        
        if (toggleBtn) {
            const isStoredCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
            
            // Set initial state berdasarkan localStorage
            if (isStoredCollapsed) {
                sidebar.classList.add('collapsed');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                
                // Tutup semua submenu
                document.querySelectorAll('.submenu').forEach(sub => {
                    sub.classList.remove('open');
                });
            }

            toggleBtn.addEventListener('click', function() {
                closeFlyout();
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                localStorage.setItem('sidebar_collapsed', isCollapsed);
                
                if(isCollapsed) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    
                    document.querySelectorAll('.submenu').forEach(sub => {
                        sub.classList.remove('open');
                    });
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
            });
        }
        
        // Submenu toggle functionality (hanya saat expanded)
        document.querySelectorAll('.menu-link .submenu-arrow').forEach(arrow => {
            const parentLink = arrow.closest('.menu-link');
            if (parentLink) {
                parentLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar && sidebar.classList.contains('collapsed')) return;
                    
                    const parentLi = this.closest('.menu-item.has-sub');
                    if (parentLi) {
                        const submenu = parentLi.querySelector(':scope > .submenu');
                        if (submenu) {
                            submenu.classList.toggle('open');
                            this.classList.toggle('active-sub');
                        }
                    }
                });
            }
        });
        
        // Attach hover & focus listeners untuk Collapsed Flyout
        document.querySelectorAll('.sidebar-menu .menu-item').forEach(item => {
            // Hanya attach ke root menu-item
            if (item.closest('.submenu')) return;

            item.addEventListener('mouseenter', function() {
                openFlyoutForItem(this);
            });
            item.addEventListener('mouseleave', function() {
                scheduleHideFlyout();
            });
            item.addEventListener('focusin', function() {
                openFlyoutForItem(this);
            });
            item.addEventListener('focusout', function() {
                scheduleHideFlyout();
            });
        });

        // Event listener pada Flyout Card
        if (flyout) {
            flyout.addEventListener('mouseenter', function() {
                clearTimeout(hideFlyoutTimer);
            });
            flyout.addEventListener('mouseleave', function() {
                scheduleHideFlyout();
            });
        }

        // Tutup flyout saat scroll atau resize
        window.addEventListener('resize', closeFlyout);
        document.querySelector('.sidebar-menu')?.addEventListener('scroll', closeFlyout);
        document.addEventListener('click', function(e) {
            if (flyout && !flyout.contains(e.target) && !e.target.closest('.sidebar-menu')) {
                closeFlyout();
            }
        });

        // Tooltip fallback untuk collapsed mode
        document.querySelectorAll('.menu-link').forEach(link => {
            const spanText = link.querySelector('.menu-text');
            if(spanText && !link.getAttribute('data-tooltip')) {
                link.setAttribute('data-tooltip', spanText.innerText.trim());
            }
        });
    });
</script>
@endpush