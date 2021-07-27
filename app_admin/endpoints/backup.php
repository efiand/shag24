<?php

$standaloneMode = false;
$path = '../../';

if (!isset($env)) {
	$standaloneMode = true;
	$path = '';
	$env = json_decode(file_get_contents('env.json'), true);
}

$backup_path = $path . 'backup/';

// Удаляем все файлы старше недели
if ($handle = opendir($backup_path)) {
	while (false !== ($entry = readdir($handle))) {
		if ($entry != '.' && $entry != '..') {
			$backup_file = $backup_path . '/' . $entry;
			$time_diff = $_SERVER['REQUEST_TIME'] - filemtime($backup_file);

			if ($time_diff > 604800) {
				unlink($backup_file);
			}
		}
	}
	closedir($handle);
}

require_once $path . 'vendor/autoload.php';

use Ifsnop\Mysqldump as IMysqldump;

try {
	$backup_filename = time() . '.sql';
	$dump = new IMysqldump\Mysqldump('mysql:host=' . $env['db_ip'] . ';dbname=' . $env['db_name'], $env['db_user'], $env['db_pass']);
	$dump->start($backup_path . '/' . $backup_filename);
	$res = 'OK: ' . $backup_filename . ' created at ' . date('r', $_SERVER['REQUEST_TIME']);
	if ($standaloneMode) {
		exit($res);
	}
	$_['res'] = $res;
} catch (Exception $e) {
	http_response_code(500);
	if ($standaloneMode) {
		exit('mysqldump-php error: ' . $e->getMessage());
	}
	$_['res'] = $is_dev ? $e : $e->getMessage();
}
