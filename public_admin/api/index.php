<?php

$root_path = '../../';
require_once $root_path . 'app_admin/core.php';

if (file_exists($endpoint = $root_path . 'app_admin/endpoints' . $payload['page'] . '.php')) {
	require_once $endpoint;
	if ($is_dev && isset($query)) {
		$_['query'] = $query;
	}
} else {
	$_['error'] = 'Endpoint не поддерживается!';
}

header('Content-Type: application/json');
exit(json_encode($_ ?? ['ok' => true], JSON_UNESCAPED_UNICODE));
