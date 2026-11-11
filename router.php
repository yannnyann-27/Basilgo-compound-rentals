<?php
// Only needed for PHP's built-in dev server:  php -S localhost:8000 router.php
// (Apache ignores this file and uses data/.htaccess instead.) Stops the browser from reading db.json, which holds password hashes.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (preg_match('#^/(data|models|config|includes)(/|$)#', $path)) { http_response_code(403); exit('Forbidden'); }
return false; // serve everything else normally
