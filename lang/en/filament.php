<?php

return [
    'brand_name' => 'Ab Proof of Play',
    'login' => [
        'heading' => 'Log in to your account',
        'forgot_password' => 'Forgot your password?',
    ],
    'forms' => [
        'buttons' => [
            'submit' => 'Save',
            'save' => 'Save',
            'create' => 'Create',
            'cancel' => 'Cancel',
        ],
        'fields' => [
            'search' => 'Search...',
        ],
    ],

    'tables' => [
        'actions' => [
            'edit' => 'Edit',
            'delete' => 'Delete',
            'view' => 'View',
        ],
        'filters' => [
            'search' => 'Search...',
        ],
        'empty' => 'No records found',
    ],

    'actions' => [
        'modal' => [
            'submit' => 'Confirm',
            'cancel' => 'Cancel',
        ],
    ],

    'notifications' => [
        'created' => 'Created successfully.',
        'saved' => 'Saved successfully.',
        'deleted' => 'Deleted successfully.',
        'error' => 'There was an error. Please try again.',
    ],

    'infolists' => [
        'buttons' => [
            'edit' => 'Edit',
        ],
    ],
    'resources' => [
        'user' => [
            'navigationLabel' => 'Users',
            'role' => [
                'options' => [
                    'user' => 'User',
                    'admin' => 'Admin',
                ],
            ],
        ],
        'client' => [
            'navigationLabel' => 'Clients',
        ],
    ],
    'pages' => [
        'view_devices' => [
            'title' => 'View Devices',
            'navigation_label' => 'View Devices',
            'form' => [
                'select_client' => 'Select Client',
                'placeholder' => 'Choose a client to view devices...',
            ],
            'columns' => [
                'site_name' => 'Site Name',
                'app_name' => 'App Name',
                'site_id' => 'Site ID',
                'device_id' => 'Device ID',
                'display_id' => 'Display ID',
                'last_updated' => 'Last Updated',
            ],
            'modal' => [
                'heading' => 'Device Details: :site',
                'description' => ':device / :display',
                'cancel' => 'Close',
            ],
            'empty' => [
                'heading' => 'No devices found',
                'description' => 'Select a client to view devices or no data available.',
            ],
            'heading' => 'Devices for: :client',
        ],
        'view_slides' => [
            'title' => 'View Slides',
            'navigation_label' => 'View Slides',
            'form' => [
                'select_client' => 'Select Client',
                'placeholder' => 'Choose a client to view slides...',
            ],
            'columns' => [
                'slide_id' => 'Slide ID',
                'name' => 'Name',
                'type' => 'Type',
                'duration_seconds' => 'Duration (s)',
                'path' => 'Path',
                'last_updated' => 'Last Updated',
            ],
            'modal' => [
                'heading' => 'Slide Details: :name',
                'subheading' => 'ID: :id - Type: :type',
                'cancel' => 'Close',
                'view_image' => 'View Image',
                'raw_record' => 'Raw Record',
                'no_image' => 'No image',
            ],
            'empty' => [
                'heading' => 'No client selected',
                'description' => 'Select a client from the dropdown above to view slides.',
            ],
            'heading' => 'Slides for: :client',
        ],
    ],
];