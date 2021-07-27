<?php

// Типы мероприятий: логика получения - как на странице управления ими
require_once '../../app_admin/endpoints/event-types.php';

// Места проведения для назначения на иероприятие
$places = get_db_data('SELECT * FROM data_places ORDER BY title');
foreach($places as $place) {
	$_['places'][] = $place;
}

// Места проведения в списке фильтров: выбираем только те, для которых есть мероприятия
$_['filterPlaces'] = [];
$filter_places = get_db_data('SELECT * FROM data_places WHERE id in
	(SELECT DISTINCT place_id FROM data_events WHERE place_id IS NOT NULL AND adminzone = "' . $adminzone .'") ORDER BY title');
foreach($filter_places as $place) {
	$_['filterPlaces'][] = $place;
}

// Тренеры
$people = get_db_data('SELECT id, name FROM data_people WHERE adminzone = "' . $adminzone
	. '" and trainer_flag = 1 ORDER BY name');
foreach($people as $unit) {
	$_['people'][] = $unit;
}

$query = 'SELECT * FROM data_events WHERE adminzone = "' . $adminzone . '"';
if ($payload['keywords']) {
	$keywords = trim($payload['keywords']);

	$query .= ' AND (';
	if (preg_match('/^[\d, ]*\d$/', $keywords)) {
		$query .= 'id IN (' . $keywords . ')';
	} else {
		$people_subquery = 'SELECT id FROM data_people WHERE name LIKE "%' . $keywords . '%"';
		$query .= 'title LIKE "%' . $keywords . '%" OR venue LIKE "%'
			. $keywords . '%" OR link LIKE "%' . $keywords . '%" OR people_id IN (' . $people_subquery . ')';
	}
	$query .= ')';

}
if ($payload['eventType'] !== '') {
	$type = (int) $payload['eventType'];
	if ($type === -1) {
		// Не Шодхан
		$query .= ' AND (type_id > 3 OR type_id IS NULL)';
	} else if ($type === 0) {
		// Шодхан
		$query .= ' AND type_id < 4';
	} else {
		// Конкретный тип
		$query .= ' AND type_id = ' . $payload['eventType'];
	}
}
if ($payload['startDate']) {
	$query .= ' AND datediff(datetime_start, "' . $payload['startDate'] . '") >= 0';
}
if ($payload['endDate']) {
	$query .= ' AND datediff(datetime_end, "' . $payload['endDate'] . '") <= 0';
}
if ($payload['place']) {
	$query .= ' AND place_id = ' . $payload['place'];
}
$query .= ' AND is_published = ' . (int) $payload['publishMode'] . ' ORDER BY datetime_start';

$_['events'] = [];
$events = get_db_data($query);
foreach ($events as $event) {
	// Если событие подключено во фрагментах, то его удалять нельзя!
	$event['deletable'] = (int) get_db_data('SELECT COUNT(id) AS id FROM data_fragments
		WHERE type = "event" AND value = "' . $event['id'] . '"', 'single')['id'] === 0;

	$event['is_published'] = (bool) $event['is_published'];

	if (file_exists($event_img_dir . $event['id'] . '.jpg')) {
		$event['img'] = $event['id'] . '.jpg';
	}
	$_['events'][] = $event;
}
