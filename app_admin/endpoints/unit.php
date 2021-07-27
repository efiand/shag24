<?php

// Локации для назначения на человека
$places = get_db_data('SELECT * FROM data_places ORDER BY title');
foreach($places as $place) {
	$_['places'][] = $place;
}

$unit_query = 'SELECT * FROM data_people WHERE id = ' . $payload['unitId'] . ' AND adminzone = "' . $adminzone . '"';
$videos_query = 'SELECT * FROM people_video WHERE people_id = ' . $payload['unitId']
	. ' AND adminzone = "' . $adminzone . '" ORDER BY sort_order';

$unit = get_db_data($unit_query, 'single');
$_['videos'] = [];
$videos = get_db_data($videos_query);
if (file_exists($avatar_dir . $unit['id'] . '.jpg')) {
	$unit['img'] = $unit['id'] . '.jpg';
}
foreach ($videos as $video) {
	$_['videos'][] = $video;
}
$_['unit'] = $unit;
