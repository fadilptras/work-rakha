<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Idle Timeout
    |--------------------------------------------------------------------------
    |
    | Berapa detik admin boleh tidak aktif sebelum otomatis di-logout.
    | Default 600 detik (10 menit). 
    |
    */
    'admin_idle_timeout' => env('ADMIN_IDLE_TIMEOUT', 600),

];