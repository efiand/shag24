<?php

session_start();

$is_dev = file_exists($root_path . 'package.json') || isset($_GET['debug']);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', (int) $is_dev);
ini_set('display_startup_errors', (int) $is_dev);

$payload = json_decode($_GET['payload'] ?? $_POST['payload'] ?? file_get_contents('php://input') ?? '{}', true);
if ($is_dev) {
	$_['payload'] = $payload;
}

$env = json_decode(file_get_contents($root_path . 'env.json'), true);
$db = mysqli_connect($env['db_ip'], $env['db_user'], $env['db_pass'], $env['db_name']);
mysqli_set_charset($db, 'utf8');

if (false !== strpos($_SERVER['HTTP_HOST'], 'khara')) {
	$adminzone = 'khara';
} else {
	$adminzone = 'shag24';
}
$media_dir = $root_path . 'public_html/media/' . $adminzone . '/';
$avatar_dir = $root_path . 'public_html/media/people/';
$event_img_dir = $root_path . 'public_html/media/events/';

require_once $root_path . 'app_admin/utils.php';
