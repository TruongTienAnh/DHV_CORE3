<?php
if (!defined('ECLO')) die("Hacking attempt");
$requests = [
    'home' => "controllers/core/frontend/home.php",
    'consultation' => "controllers/core/frontend/consultation.php",
    'contact' => "controllers/core/frontend/contact.php",

    'library' => "controllers/core/frontend/library.php",

    'services' => "controllers/core/frontend/services.php",
    'services-detail' => "controllers/core/frontend/services-detail.php",

    'news' => "controllers/core/frontend/news.php",
    'projects' => "controllers/core/frontend/projects.php",



    // 'admin' => "controllers/core/back-end/admin.php",
    // 'main' => "controllers/core/back-end/main.php",
    // 'users' => "controllers/core/back-end/users.php",
];

foreach ($requests as $key => $controller) {
    $setRequest[] = [
        "key" => $key,
        "controllers" =>  $controller,
    ];
}