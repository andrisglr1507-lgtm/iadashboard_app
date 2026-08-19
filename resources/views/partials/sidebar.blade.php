<aside class="sidebar" id="sidebar">

    <div class="sidebar-menu">
        <ul class="menu-list" id="menuList">
            @foreach(config('sidebar', []) as $item)
                @include('partials.menu-item', ['item' => $item])
            @endforeach
        </ul>
    </div>
    
    <div style="padding: 20px 16px; border-top: 1px solid #eff3f8; font-size: 0.7rem; color:#8ba0b5; text-align:center;">
        <span class="menu-text">v1.0 · Laravel</span>
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
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                if(isCollapsed) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    // Tutup semua submenu saat collapsed
                    document.querySelectorAll('.submenu').forEach(sub => {
                        sub.classList.remove('open');
                    });
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
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