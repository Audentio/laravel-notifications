<?php

return [
    'push_enabled' => false,
    'push_max_failures' => 5,

    'notification_kinds' => [],

    'push_handler_classes' => [
        'expo' => \Audentio\LaravelNotifications\PushHandlers\ExpoPushHandler::class,
    ],

    'graphQL_schema_overrides' => [],
    'custom_notification_channel_names' => [],
];