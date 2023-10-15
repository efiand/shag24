<?php

$event = $payload['event'];

$title = $event['title'] ? trim($event['title']) : '';
$venue = $event['venue'] ? trim($event['venue']) : '';
$link = $event['link'] ? trim($event['link']) : '';

$queryBody = '';
if ($event['type_id']) {
	$queryBody .= ' type_id = ' . $event['type_id'] . ',';
}
if ($title) {
	$queryBody .= ' title = "' . prepare_post_data($title) . '",';
} else if ($event['type_id']) {
	$queryBody .= ' title = (SELECT title FROM data_event_types WHERE id = ' . $event['type_id'] . '),';
}
if ($event['people_id']) {
	$queryBody .= ' people_id = ' . $event['people_id'] . ',';
}
if ($event['datetime_start']) {
	$queryBody .= ' datetime_start = "' . $event['datetime_start'] . '",';
}
if ($event['datetime_end']) {
	$queryBody .= ' datetime_end = "' . $event['datetime_end'] . '",';
} else if ($event['datetime_start']) {
	$queryBody .= ' datetime_end = "' . $event['datetime_start'] . '",';
}
if ($event['place_id']) {
	$queryBody .= ' place_id = ' . $event['place_id'] . ',';
}
if ($venue) {
	$queryBody .= ' venue = "' . prepare_post_data($venue) . '",';
} else {
	$queryBody .= ' venue = NULL,';
}
if ($link) {
	$queryBody .= ' link = "' . prepare_post_data($link) . '",';
} else {
	$queryBody .= ' link = NULL,';
}
if ($event['price']) {
	$queryBody .= ' price = ' . $event['price'] . ',';
}
$queryBody .= ' adminzone = "' . $adminzone . '", is_published = ' . (int) $event['is_published'];

$id = (int) $event['id'];
if ($id > 0) {
	$query = 'UPDATE data_events SET' . $queryBody . ' WHERE id = ' . $event['id'];
} else {
	$query = 'INSERT INTO data_events SET' . $queryBody;
	$id = get_db_data('SELECT AUTO_INCREMENT AS id FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = "data_people"', 'single')['id'];
}

set_db_data($query);

if (isset($_FILES['file'])) {
	$upload_status = move_uploaded_file($_FILES['file']['tmp_name'], $event_img_dir . $id . '.jpg');
	if ($is_dev) {
		$_['file'] = $_FILES['file'];
		$_['id'] = $id;
		$_['uploadStatus'] = $upload_status;
	}
} else if (isset($event['modifiedImage']) && $event['modifiedImage'] && file_exists($event_img_dir . $id . '.jpg')) {
	unlink($event_img_dir . $id . '.jpg');
}
