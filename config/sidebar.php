<?php

return [
    // --- SO DC APPLICATION MENUS ---
    [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'route' => 'dashboard',
    ],
    
    // Master Data
    [
        'title' => 'Master Data',
        'icon' => 'fas fa-database',
        'submenu' => [
            [
                'title' => 'Branches',
                'icon' => 'fas fa-code-branch',
                'route' => 'sodc.branches.index', // placeholder route
            ],
            [
                'title' => 'Warehouses',
                'icon' => 'fas fa-warehouse',
                'route' => 'sodc.warehouses.index', // placeholder route
            ],
            [
                'title' => 'Bins Location',
                'icon' => 'fas fa-box-open',
                'route' => 'sodc.bins.index', // placeholder route
            ],
            [
                'title' => 'Products (SKU)',
                'icon' => 'fas fa-boxes',
                'route' => 'sodc.products.index', // placeholder route
            ],
            [
                'title' => 'Teams & Members',
                'icon' => 'fas fa-users-cog',
                'route' => 'sodc.teams.index', // placeholder route
            ],
        ],
    ],

    // Opname Management
    [
        'title' => 'Opname Management',
        'icon' => 'fas fa-clipboard-list',
        'submenu' => [
            [
                'title' => 'WMS References (Snapshots)',
                'icon' => 'fas fa-file-download',
                'route' => 'sodc.references.index',
            ],
            [
                'title' => 'Opname Sessions',
                'icon' => 'fas fa-calendar-check',
                'route' => 'sodc.sessions.index',
            ],
            [
                'title' => 'Team Assignments',
                'icon' => 'fas fa-tasks',
                'route' => 'sodc.assignments.index',
            ],
        ],
    ],

    // Opname Results
    [
        'title' => 'Results & Variance',
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
    ],

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
