<?php

$video = $payload['video'];

$name = $video['name'] ? trim($video['name']) : '';
$url = $video['url'] ? trim($video['url']) : '';

$queryBody = ' name = "' . prepare_post_data($name) . '",';
if (filter_var($url, FILTER_VALIDATE_URL)) {
	$queryBody .= ' url = "' . prepare_post_data(str_replace('.be/', 'be.com/embed/', $url)) . '",';
} else {
	$queryBody .= ' url = "",';
}
if ($video['sort_order']) {
	$queryBody .= ' sort_order = ' . $video['sort_order'] . ',';
}
$queryBody .= ' people_id = ' . $video['people_id'] . ', adminzone = "' . $adminzone . '"';

$id = (int) $video['id'];
if ($id > 0) {
	$query = 'UPDATE people_video SET' . $queryBody . ' WHERE id = ' . $id;
} else {
	$query = 'INSERT INTO people_video SET' . $queryBody;
	$id = get_db_data('SELECT AUTO_INCREMENT AS id FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = "people_video"', 'single')['id'];
}

set_db_data($query);
