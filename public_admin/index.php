<?php

$root_path = '../';
require_once $root_path . 'app_admin/core.php';

$version = get_db_data('SELECT value FROM parameters WHERE name = "version"', 'single')['value'];

?><!DOCTYPE html>
<html class="admin" lang="ru" prefix="og: http://ogp.me/ns#">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta http-equiv="Cache-Control" content="no-cache">
	<title>Панель управления</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
	<link rel="stylesheet" href="js/style.min.css?version=<?=$version?>">
</head>

<body>
	<script>
		window.VERSION = '<?=$version?>';
	</script>
	<script src="js/script.min.js?version=<?=$version?>"></script>
</body>
</html>
