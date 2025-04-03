<?php
define('IS_WINDOWS', DIRECTORY_SEPARATOR === '\\');
define('SITE_ROOT', IS_WINDOWS ? 'c:/xampp/htdocs' : '/var/www/html');

function get_site_path($path) {
    return IS_WINDOWS ? 
        str_replace('/', '\\', SITE_ROOT . $path) : 
        SITE_ROOT . $path;
}

function get_web_path($path) {
    return str_replace('\\', '/', $path);
}

function get_json_path($filename, $dir) {
    return get_site_path("/$dir/$filename.json");
}
