<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Permissions Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the master list of all available permissions in the
    | system. When you add a new feature, add its permission here. The
    | `php artisan rbac:sync` command will insert them into the DB.
    |
    */

    'groups' => [
        'POS' => [
            'pos.access'      => 'Access the POS register and create orders.',
            'pos.payments'    => 'Process payments and finalize orders.',
            'pos.void'        => 'Void or delete an existing order.',
            'pos.remove_item' => 'Remove items from an active order.',
            'pos.discount'    => 'Apply discounts to orders or items.',
            'pos.shifts'      => 'Open and close cash register shifts.',
        ],

        'KDS' => [
            'kds.access'      => 'Access the Kitchen Display System.',
            'kds.update'      => 'Update order statuses in the kitchen (Preparing, Ready).',
        ],

        'Admin' => [
            'admin.dashboard' => 'View the admin dashboard and basic stats.',
            'admin.reports'   => 'View detailed financial and sales reports.',
            'admin.users'     => 'Manage staff users and accounts.',
            'admin.roles'     => 'Manage roles and assign permissions.',
            'admin.catalog'   => 'Manage menu categories, products, and variants.',
            'admin.inventory' => 'Manage inventory, stock, and recipes.',
            'admin.pos_setup' => 'Manage physical POS setup (Tables, Printers, etc).',
            'admin.customers' => 'Manage CRM and customer profiles.',
        ],
    ],
];
