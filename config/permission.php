<?php

return [
    'modules' => [
        'dashboard'   => ['view'],
        'staff'       => ['view', 'create', 'update', 'delete'],
        'departments' => ['view', 'create', 'update', 'delete'],
        'roles'       => ['view', 'manage'],
    ]
];