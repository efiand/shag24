<?php

$unit = $payload['unit'];

$login = $unit['login'] ? trim($unit['login']) : '';
$name = $unit['name'] ? trim($unit['name']) : '';
$description = $unit['description'] ? trim($unit['description']) : '';
$email = $unit['email'] ? trim($unit['email']) : '';
$url = $unit['url'] ? trim($unit['url']) : '';
$tel = $unit['tel'] ? trim($unit['tel']) : '';

if ($name) {
	$queryBody = ' name = "' . prepare_post_data($name) . '",';
	if ($description) {
		$queryBody .= ' description = "' . prepare_post_data($description) . '",';
	} else {
		$queryBody .= ' description = NULL,';
	}
	if ($login) {
		$queryBody .= ' login = "' . prepare_post_data($login) . '",';
	} else {
		$queryBody .= ' login = NULL,';
	}
	if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$queryBody .= ' email = "' . prepare_post_data($email) . '",';
	} else {
		$queryBody .= ' email = NULL,';
	}
	if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
		$queryBody .= ' url = "' . prepare_post_data($url) . '",';
	} else {
		$queryBody .= ' url = NULL,';
	}
	if ($tel) {
		$tel = preg_replace('/^(8|7){1}/', '+7', preg_replace('/[^0-9+]/', '', $tel));
		$queryBody .= ' tel = "' . prepare_post_data($tel) . '",';
	} else {
		$queryBody .= ' tel = NULL,';
	}
	if ($unit['place_id']) {
		$queryBody .= ' place_id = ' . $unit['place_id'] . ',';
	} else {
		$queryBody .= ' place_id = NULL,';
	}
	if (isset($unit['rawPassword']) && $unit['rawPassword']) {
		$queryBody .= ' password = "' . password_hash($unit['rawPassword'], PASSWORD_DEFAULT) . '",';
	}
	$queryBody .= ' adminzone = "' . $adminzone . '", trainer_flag = ' . (int) $unit['trainer_flag']
		. ', shodhan_flag = ' . (int) $unit['shodhan_flag'] . ', shodhan_cert_flag = ' . (int) $unit['shodhan_cert_flag'];

	$id = (int) $unit['id'];
	if ($id > 0) {
		$query = 'UPDATE data_people SET' . $queryBody . ' WHERE id = ' . $id;
	} else {
		$query = 'INSERT INTO data_people SET' . $queryBody;
		$id = get_db_data('SELECT AUTO_INCREMENT AS id FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = "data_people"', 'single')['id'];
	}

	set_db_data($query);

	if (isset($_FILES['file'])) {
		$upload_status = move_uploaded_file($_FILES['file']['tmp_name'], $avatar_dir . $id . '.jpg');
		if ($is_dev) {
			$_['file'] = $_FILES['file'];
			$_['id'] = $id;
			$_['uploadStatus'] = $upload_status;
		}
	} else if (isset($unit['modifiedImage']) && $unit['modifiedImage'] && file_exists($avatar_dir . $id . '.jpg')) {
		unlink($avatar_dir . $id . '.jpg');
	}
}
