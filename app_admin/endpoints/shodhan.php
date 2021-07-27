<?php

$event_types = get_db_data('SELECT * FROM data_event_types WHERE id < 4 ORDER by title');
$_['eventTypes'] = [];
foreach($event_types as $event_type) {
	$_['eventTypes'][] = $event_type;
}

// Места проведения для назначения на иероприятие
$places = get_db_data('SELECT * FROM data_places ORDER BY title');
foreach($places as $place) {
	$_['places'][] = $place;
}

// Тренер
$id = $payload['id'];
$unit = get_db_data('SELECT * FROM data_people WHERE id = ' . $id, 'single');
if (isset($unit['id'])) {
	$unit['trainer_flag'] = (bool) $unit['trainer_flag'];
	$unit['shodhan_flag'] = (bool) $unit['shodhan_flag'];
	$unit['shodhan_cert_flag'] = (bool) $unit['shodhan_cert_flag'];
	if (file_exists($avatar_dir . $unit['id'] . '.jpg')) {
		$unit['img'] = $unit['id'] . '.jpg';
	}
}
$_['unit'] =  $unit;

$_['events'] = [];
$events = get_db_data('SELECT * FROM data_events WHERE type_id < 4 AND people_id = ' . $id . ' ORDER BY datetime_start');
foreach ($events as $event) {
	// Если событие подключено во фрагментах, то его удалять нельзя!
	$event['deletable'] = (int) get_db_data('SELECT COUNT(id) AS id FROM data_fragments
		WHERE type = "event" AND value = "' . $event['id'] . '"', 'single')['id'] === 0;

	$event['is_published'] = (bool) $event['is_published'];
	$_['events'][] = $event;
}
