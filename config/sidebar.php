<?php

return [
    // --- SO DC APPLICATION MENUS ---
    [
        'header' => 'Dash',
    ],

    [
        'title' => 'Dashboard',
        'icon' => 'ti ti-home-stats',
        'route' => 'dashboard',
    ],

// Header for Branch
    
    [
        'header' => 'Stock Opname DC',
    ],
    
    // Master Data
    [
        'title' => 'Master Data',
        'icon' => 'ri-database-2-line',
        'submenu' => [
            [
                'title' => 'Branches',
                'icon' => 'ri-git-branch-line',
                'route' => 'sodc.branches.index', // placeholder route
                'roles' => ['SUPER_ADMIN', 'ADMIN'],
            ],
            [
                'title' => 'Warehouses',
                'icon' => 'ri-building-line',
                'route' => 'sodc.warehouses.index', // placeholder route
                'roles' => ['SUPER_ADMIN', 'ADMIN'],
            ],
            [
                'title' => 'Bins Location',
                'icon' => 'ri-archive-line',
                'route' => 'sodc.bins.index', // placeholder route
                'roles' => ['SUPER_ADMIN', 'ADMIN'],
            ],
            [
                'title' => 'Products',
                'icon' => 'ri-box-3-line',
                'route' => 'sodc.products.index', // placeholder route
                'roles' => ['SUPER_ADMIN', 'ADMIN'],
            ],
            [
                'title' => 'Teams & Members',
                'icon' => 'ri-team-line',
                'route' => 'sodc.teams.index', // placeholder route
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'User Management',
                'icon' => 'ri-user-settings-line',
                'route' => 'users.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN'],
            ],
        ],
    ],

    // Opname Management
    [
        'title' => 'SO Management',
        'icon' => 'ti ti-clipboard-list',
        'submenu' => [
            [
                'title' => 'WMS References',
                'icon' => 'ri-file-download-line',
                'route' => 'sodc.references.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Opname Sessions',
                'icon' => 'ri-calendar-check-line',
                'route' => 'sodc.sessions.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Assignments (Mode Gudang)',
                'icon' => 'ri-task-line',
                'route' => 'sodc.assignments.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Assignments (Mode Lorong)',
                'icon' => 'ri-route-line',
                'route' => 'sodc.assignments_bulk.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Rekap Bad Stock',
                'icon' => 'ri-delete-bin-5-line',
                'route' => 'sodc.bad_stock.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME', 'AUDITOR'],
            ]
        ],
    ],

    // Opname Results
    [
        'title' => 'Results & Variance',
        'icon' => 'ti ti-file-report',
        'submenu' => [
            [
                'title' => 'Input SO by Team',
                'icon' => 'ri-fingerprint-line',
                'route' => 'sodc.input_by_team.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Tracking Opname',
                'icon' => 'ri-map-pin-2-line',
                'route' => 'sodc.tracking.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME', 'AUDITOR'],
            ],
            [
                'title' => 'Count Results',
                'icon' => 'ri-checkbox-multiple-line',
                'route' => 'sodc.results.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME', 'AUDITOR'],
            ],
        ],
    ],


    [
        'header' => 'Stock Opname Branch',
    ],

    [
        'title' => 'SO Branch',
        'icon' => 'ri-store-2-line',
        'submenu' => [
            [
                'title' => 'Sesi Opname Cabang',
                'icon' => 'ri-calendar-check-line',
                'route' => 'sob.sessions.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Area & Zona Cabang',
                'icon' => 'ri-map-pin-range-line',
                'route' => 'sob.zones.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Penugasan Petugas',
                'icon' => 'ri-user-shared-line',
                'route' => 'sob.assignments.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME'],
            ],
            [
                'title' => 'Tracking SO',
                'icon' => 'ri-radar-line',
                'route' => 'sob.sweep_headers.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME', 'AUDITOR'],
            ],
            [
                'title' => 'Komparasi Hasil SO ',
                'icon' => 'ri-compare-arrows-line',
                'route' => 'sob.tag_results.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME', 'AUDITOR'],
            ],
            [
                'title' => 'Rekonsiliasi Stok Cabang',
                'icon' => 'ri-file-chart-line',
                'route' => 'sob.reconciliations.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'HEAD_OPNAME', 'AUDITOR'],
            ],
        ],
    ],

    [
        'header' => 'Other Apps',
    ],

    [
        'title' => 'Konfirmasi Faktur',
        'icon' => 'ri-store-2-line',
        'submenu' => [
            [
                'title' => 'Data Pelanggan',
                'icon' => 'ri-user-star-line',
                'route' => 'konfirmasi_faktur.customers.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'AUDITOR'],
            ],
            [
                'title' => 'Input List Bill',
                'icon' => 'ri-map-pin-range-line',
                'route' => 'sob.zones.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'AUDITOR'],
            ],
            [
                'title' => 'Hasil Kunjungan',
                'icon' => 'ri-user-shared-line',
                'route' => 'sob.assignments.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'AUDITOR'],
            ],
            [
                'title' => 'Hasil Pembaharuan Geotag',
                'icon' => 'ri-radar-line',
                'route' => 'sob.sweep_headers.index',
                'roles' => ['SUPER_ADMIN', 'ADMIN', 'AUDITOR'],
            ],
        ],
    ],


    // Header for Branch
    /*
    [
        'header' => 'Stock Opname CSAN',
    ],

    [
        'title' => 'Session Management',
        'icon' => 'fas fa-chart-bar',
        'submenu' => [
            [
                'title' => 'Sync Logs (Mobile)',
                'icon' => 'fas fa-sync-alt',
                'route' => 'sodc.sync_logs.index',
            ],
            [
                'title' => 'Count Results',
                'icon' => 'fas fa-check-double',
                'route' => 'sodc.results.index',
            ],
            [
                'title' => 'Variance & Approvals',
                'icon' => 'fas fa-file-signature',
                'route' => 'sodc.approvals.index',
            ],
        ],
    ],*/

    





    // --- OLD MENUS (COMMENTED OUT FOR NOW) ---
    /*
    [
        'title' => 'Session Opname',
        'icon' => 'fas fa-clipboard-list',
        'route' => 'sessions.index',
    ],
    [
        'title' => 'Stock Opname',
        'icon' => 'fas fa-chart-line',
        'submenu' => [
            [
                'title' => 'Single Mode',
                'icon' => 'fas fa-file-alt',
                'submenu' => [
                    [
                        'title' => 'List Records',
                        'icon' => 'fas fa-list',
                        'route' => 'single_mode.records',
                    ],
                    [
                        'title' => 'Recount',
                        'icon' => 'fas fa-users',
                        'route' => 'single_mode.recount',
                    ],
                    [
                        'title' => 'Assigment',
                        'icon' => 'fas fa-clipboard-check',
                        'route' => 'single_mode.assignments',
                    ],
                ],
            ],
            [
                'title' => 'A Vs B',
                'icon' => 'fas fa-file-alt',
                'submenu' => [
                    [
                        'title' => 'List Records',
                        'icon' => 'fas fa-chart-pie',
                        'route' => 'reports.sales',
                    ],
                    [
                        'title' => 'Recount',
                        'icon' => 'fas fa-users',
                        'route' => 'reports.users',
                    ],
                    [
                        'title' => 'Assigment',
                        'icon' => 'fas fa-users',
                        'route' => 'reports.users',
                    ],
                ],
            ],
        ],
    ],
    [
        'title' => 'Master Data',
        'icon' => 'fas fa-folder-tree',
        'submenu' => [
            [
                'title' => 'Master Product',
                'icon' => 'fas fa-boxes',
                'route' => 'master.products.index',
            ],
            [
                'title' => 'List Users',
                'icon' => 'fas fa-images',
                'submenu' => [
                    [
                        'title' => 'Users',
                        'icon' => 'fas fa-image',
                        'route' => 'media.images',
                    ],
                    [
                        'title' => 'Videos',
                        'icon' => 'fas fa-video',
                        'route' => 'media.videos',
                    ],
                ],
            ],
        ],
    ],

    [
        'title' => 'BA Pemeriksaan',
        'icon' => 'fas fa-folder-tree',
        'submenu' => [
            [
                'title' => 'BA CSAN',
                'icon' => 'fas fa-boxes',
                'route' => 'ba_pemeriksaan.index',
            ],
        ],
    ],

    [
        'title' => 'SO DC Bayur',
        'icon' => 'fas fa-folder-tree',
        'submenu' => [
            [
                'title' => 'List Recount',
                'icon' => 'fas fa-boxes',
                'route' => 'ba_pemeriksaan.index',
            ],
        ],
    ],

    [
        'title' => 'Settings',
        'icon' => 'fas fa-sliders-h',
        'submenu' => [
            [
                'title' => 'Profile',
                'icon' => 'fas fa-user-cog',
                'route' => 'settings.profile',
            ],
            [
                'title' => 'Security',
                'icon' => 'fas fa-shield-alt',
                'route' => 'settings.security',
            ],
        ],
    ],
    */
];
