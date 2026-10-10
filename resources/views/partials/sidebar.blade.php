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

    <div class="sidebar-menu" style="flex-grow: 1; overflow-y: auto;">
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

        @auth
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;" class="header-text">
            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #1ABB9C; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; flex-shrink: 0;">
                    {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->username, 0, 1)) }}
                </div>
                <div style="display: flex; flex-direction: column; overflow: hidden;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #ECF0F1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Auth::user()->full_name ?? Auth::user()->username }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0; flex-shrink: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #8ba0b5; cursor: pointer; padding: 4px; transition: color 0.2s;" onmouseover="this.style.color='#e74c3c'" onmouseout="this.style.color='#8ba0b5'" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
        @endauth
    </div>
</aside>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (toggleBtn) {
            const brandContainer = document.getElementById('headerBrandContainer');
            const brandText = document.getElementById('headerBrandText');
            
            // Cek localStorage untuk status sidebar sebelumnya
            const isStoredCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
            
            // Set initial state berdasarkan localStorage
            if (isStoredCollapsed) {
                sidebar.classList.add('collapsed');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                
                if (brandText) {
                    brandText.style.opacity = '0';
                    brandText.style.display = 'none';
                }
                
                const toggleText = document.getElementById('toggleText');
                if (toggleText) {
                    toggleText.style.opacity = '0';
                    toggleText.style.display = 'none';
                }

                document.querySelectorAll('.header-text').forEach(el => {
                    el.style.display = 'none';
                });
                
                // Tutup semua submenu
                document.querySelectorAll('.submenu').forEach(sub => {
                    sub.classList.remove('open');
                });
            }

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                // Simpan state ke localStorage
                localStorage.setItem('sidebar_collapsed', isCollapsed);
                
                if(isCollapsed) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    
                    if (brandText) {
                        brandText.style.opacity = '0';
                        setTimeout(() => { if (sidebar.classList.contains('collapsed')) brandText.style.display = 'none'; }, 200);
                    }
                    
                    const toggleText = document.getElementById('toggleText');
                    if (toggleText) {
                        toggleText.style.opacity = '0';
                        setTimeout(() => { if (sidebar.classList.contains('collapsed')) toggleText.style.display = 'none'; }, 200);
                    }
                    
                    document.querySelectorAll('.header-text').forEach(el => {
                        el.style.opacity = '0';
                        setTimeout(() => { if (sidebar.classList.contains('collapsed')) el.style.display = 'none'; }, 200);
                    });

                    // Tutup semua submenu saat collapsed
                    document.querySelectorAll('.submenu').forEach(sub => {
                        sub.classList.remove('open');
                    });
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                    
                    if (brandText) {
                        brandText.style.display = 'block';
                        setTimeout(() => brandText.style.opacity = '1', 10);
                    }

                    const toggleText = document.getElementById('toggleText');
                    if (toggleText) {
                        toggleText.style.display = 'block';
                        setTimeout(() => toggleText.style.opacity = '1', 10);
                    }

                    document.querySelectorAll('.header-text').forEach(el => {
                        el.style.display = 'block';
                        setTimeout(() => el.style.opacity = '1', 10);
                    });
                }
            });
        }
        
        // Submenu toggle functionality
        document.querySelectorAll('.menu-link .submenu-arrow').forEach(arrow => {
            const parentLink = arrow.closest('.menu-link');
            if (parentLink) {
                parentLink.addEventListener('click', function(e) {
                    e.preventDefault();
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
        
        // Tooltip untuk collapsed mode
        document.querySelectorAll('.menu-link').forEach(link => {
            const spanText = link.querySelector('.menu-text');
            if(spanText && !link.getAttribute('data-tooltip')) {
                link.setAttribute('data-tooltip', spanText.innerText.trim());
            }
        });
    });
</script>
@endpush