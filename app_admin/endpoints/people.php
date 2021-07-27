<?php

// Локации для назначения на человека
$places = get_db_data('SELECT * FROM data_places ORDER BY title');
foreach($places as $place) {
	$_['places'][] = $place;
}

// Локации в списке фильтров: выбираем только связанные с людьми
$_['filterPlaces'] = [];
$filter_places = get_db_data('SELECT * FROM data_places WHERE id in
	(SELECT DISTINCT place_id FROM data_people WHERE place_id IS NOT NULL AND adminzone = "' . $adminzone .'") ORDER BY title');
foreach($filter_places as $place) {
	$_['filterPlaces'][] = $place;
}

$query = 'SELECT * FROM data_people WHERE adminzone = "' . $adminzone . '"';
if ($payload['keywords']) {
	$keywords = trim($payload['keywords']);
	$query .= ' AND (name LIKE "%' . $keywords . '%" OR login LIKE "%' . $keywords . '%" OR email LIKE "%'
		. $keywords . '%" OR url LIKE "%' . $keywords . '%")';
}
if ($payload['place']) {
	$query .= ' AND place_id = ' . $payload['place'];
}

if ($payload['showTrainers']) {
	$query .= ' AND trainer_flag = ' . (int) $payload['showTrainers'];
}
if ($payload['showShodhan1']) {
	$query .= ' AND shodhan_flag = ' . (int) $payload['showShodhan1'];
}
if ($payload['showShodhan2']) {
	$query .= ' AND shodhan_cert_flag = ' . (int) $payload['showShodhan2'];
}

$query .= ' ORDER BY name';

$_['people'] = [];
$people = get_db_data($query);
foreach ($people as $unit) {
	$unit['trainer_flag'] = (bool) $unit['trainer_flag'];
	$unit['shodhan_flag'] = (bool) $unit['shodhan_flag'];
	$unit['shodhan_cert_flag'] = (bool) $unit['shodhan_cert_flag'];
	if (file_exists($avatar_dir . $unit['id'] . '.jpg')) {
		$unit['img'] = $unit['id'] . '.jpg';
	}
	$_['people'][] = $unit;
}
