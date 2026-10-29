<?php
/**
 * Site configuration.
 * Most content (name, contact details, donation info, programmes) is edited
 * from the admin panel, so this file only holds technical options.
 */
return [
    // Leave null to auto-detect (works at the domain root or in a sub-folder).
    // Set manually, e.g. '/mysite', only if links come out wrong.
    'base_url'     => null,

    'timezone'     => 'Africa/Lusaka',

    // Set to true only while developing; shows PHP errors on screen.
    'debug'        => false,

    // Optional: if set, new contact/volunteer submissions are also emailed here
    // (requires PHP mail() to be configured on your host).
    'notify_email' => '',

    // Max upload size for category images, in bytes (3 MB).
    'max_upload'   => 3 * 1024 * 1024,
];
