<aside class="sidebar" id="sidebar">
    <!-- Brand Logo -->
    <div id="headerBrandContainer" style="display: flex; align-items: center; gap: 12px; height: 70px; padding-left: 20px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; white-space: nowrap;">
        <i class="fas fa-paw" style="font-size: 24px; color: #ECF0F1; flex-shrink: 0;"></i>
        <span id="headerBrandText" style="font-weight: 400; font-size: 1.25rem; color: #ECF0F1; letter-spacing: 0.5px; transition: opacity 0.2s;">Dashboard</span>
    </div>

    <div class="sidebar-menu">
        <ul class="menu-list" id="menuList">
            @foreach(config('sidebar', []) as $item)
                @include('partials.menu-item', ['item' => $item])
            @endforeach
        </ul>
    </div>
    
    <div style="padding: 20px 16px; border-top: 1px solid #eff3f8; font-size: 0.7rem; color:#8ba0b5; text-align:center;">
        <span class="menu-text">v1.0 · Dashboard</span>
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
            // Cek status saat halaman dimuat
            const brandContainer = document.getElementById('headerBrandContainer');
            const brandText = document.getElementById('headerBrandText');
            
            // Set initial state
            if (sidebar.classList.contains('collapsed')) {
                if (brandContainer) brandContainer.style.width = '42px';
                if (brandText) {
                    brandText.style.opacity = '0';
                    brandText.style.display = 'none';
                }
                document.querySelectorAll('.header-text').forEach(el => {
                    el.style.display = 'none';
                });
            }

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                if(isCollapsed) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    
                    if (brandContainer) brandContainer.style.width = '42px';
                    if (brandText) {
                        brandText.style.opacity = '0';
                        setTimeout(() => { if (sidebar.classList.contains('collapsed')) brandText.style.display = 'none'; }, 200);
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