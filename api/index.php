<?php
// Vercel routes the app's PHP URLs through this single PHP Function. Only
// known application pages are dispatchable; user-controlled paths are rejected.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);
$routes = [
    '/' => __DIR__ . '/../index.php',
    '/index.php' => __DIR__ . '/../index.php',
    '/login.php' => __DIR__ . '/../login.php',
    '/register.php' => __DIR__ . '/../register.php',
    '/logout.php' => __DIR__ . '/../logout.php',
    '/dashboard.php' => __DIR__ . '/../dashboard.php',
    '/profile.php' => __DIR__ . '/../profile.php',
    '/pages/course.php' => __DIR__ . '/../pages/course.php',
    '/pages/instructor.php' => __DIR__ . '/../pages/instructor.php',
    '/pages/schoolyear.php' => __DIR__ . '/../pages/schoolyear.php',
    '/pages/section.php' => __DIR__ . '/../pages/section.php',
    '/pages/subjects.php' => __DIR__ . '/../pages/subjects.php',
    '/pages/term.php' => __DIR__ . '/../pages/term.php',
    '/pages/users.php' => __DIR__ . '/../pages/users.php',
    '/pages/usertype.php' => __DIR__ . '/../pages/usertype.php',
    '/pages/yearlevel.php' => __DIR__ . '/../pages/yearlevel.php',
];

$script = $routes[$requestPath] ?? null;
if ($script === null || !is_file($script)) {
    http_response_code(404);
    exit('Not found.');
}

// The app uses these values to build links relative to root pages and /pages.
$_SERVER['SCRIPT_NAME'] = $requestPath;
$_SERVER['PHP_SELF'] = $requestPath;
$_SERVER['SCRIPT_FILENAME'] = $script;
require $script;
