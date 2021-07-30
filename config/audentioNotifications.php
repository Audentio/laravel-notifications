<?php

return [
    'push_enabled' => false,
    'push_max_failures' => 5,

    'push_handler_classes' => [
        'expo' => \Audentio\LaravelNotifications\PushHandlers\ExpoPushHandler::class,
    ],
];