<?php
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, '/src/') === 0 || file_exists(__DIR__ . $uri)) {
    return false;
}
header('Location: /src' . $uri, true, 302);
exit;