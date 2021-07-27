<?php

$host = $_SERVER['HTTP_HOST'];
if (false !== strpos($host, '.khara24')) {
	http_response_code(301);
	header('Location: https://' . str_replace('.khara24', '', $host) . $_SERVER['REQUEST_URI']);
	exit;
}

require_once '../app_html/core.php';
