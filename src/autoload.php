<?php

// Init composer autoload
call_user_func(function () {
    $candidates = [
        // module in vendor
        __DIR__ . '/../../../autoload.php',
    ];
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            require_once $candidate;
            return;
        }
    }
    die("Failed to include composer's autoloader, unable to continue");
});
