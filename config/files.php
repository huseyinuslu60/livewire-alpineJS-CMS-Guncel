<?php

return [
    // Max upload size in KB (default 2MB). Override via FILES_MAX_SIZE_KB in .env
    'max_size_kb' => env('FILES_MAX_SIZE_KB', 2048),
];
