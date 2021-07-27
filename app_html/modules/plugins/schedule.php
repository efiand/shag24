<?php

$_['IMG_MODE'] = false;

$filter = isset($_['SCHEDULE_FILTER']) ? trim($_['SCHEDULE_FILTER']) : '';

if ($filter) {
	$filter = ' AND (' . $filter . ')';
}

// Фильтровать ли по прошлому времени?
$past_show = $_['SCHEDULE_PAST_SHOW'] ?? false;
if (!$past_show) {
	$filter = ' AND datediff(datetime_end_str, current_timestamp) >= 0' . $filter;
}
if (isset($_['EVENTS_CITY']['alias'])) {
	$_['SCHEDULE_TITLE'] .= ': ' . $_['EVENTS_CITY']['title'];
	$filter = ' AND place_alias = "' . $_['EVENTS_CITY']['alias'] . '"' . $filter;
}

$schedule = get_db_data('SELECT * FROM view_events
	WHERE adminzone = "' . $_['ADMINZONE'] . '" AND is_published = 1' . $filter
	. ' ORDER BY datetime_start, datetime_end');

foreach ($schedule as $item) {
	if (isset($_['SCHEDULE_SHOW_IMAGES']) && $_['SCHEDULE_SHOW_IMAGES']) {
		if (file_exists('media/events/' . $item['id'] . '.jpg')) {
			$item['img'] = '//' . $_['HOST'] . '/media/events/' . $item['id'] . '.jpg';
			$item['img_alt'] = $item['title'];
			$_['IMG_MODE'] = true;
		} else if ($item['people_id'] && file_exists('media/people/' . $item['people_id'] . '.jpg')) {
			$item['img'] = '//' . $_['HOST'] . '/media/people/' . $item['people_id'] . '.jpg';
			$item['img_alt'] = $item['instructor'];
			$_['IMG_MODE'] = true;
		}
	}
	$_['SCHEDULE'][] = $item;
}
