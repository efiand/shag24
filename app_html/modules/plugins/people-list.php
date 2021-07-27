<?php

$numbers = '';
$query = 'SELECT id, name, description FROM data_people WHERE adminzone = "' . $_['ADMINZONE'] . '"';
if (isset($_['PEOPLE_LIST_NUMBERS'])) {
	$numbers = preg_replace('/\D+/', ',', $_['PEOPLE_LIST_NUMBERS']);
	$query .= ' AND id IN (' . preg_replace('/,$/', '', $numbers) . ')';
} else if (isset($_['PEOPLE_LIST_FILTER'])) {
	$query .= ' AND id IN (' . $_['PEOPLE_LIST_FILTER'] . ')';
}

if (isset($_['PEOPLE_NAME_ORDER'])) {
	$query .= ' ORDER BY name';
}

$list = get_db_data($query, 'id');

foreach ($list as &$unit) {
	if (file_exists('media/people/' . $unit['id'] . '.jpg')) {
		$unit['img'] = '//' . $_['HOST'] . '/media/people/' . $unit['id'] . '.jpg';
	} else {
		$unit['img'] = '//' . $_['HOST'] . '/media/shag24/people-blank.jpg';
	}
}

if ($numbers) {
	// Сортировка по списку вариантов из админки
	foreach (explode(',', $numbers) as $id) {
		if (isset($list[$id])) {
			$_['PEOPLE_LIST_ITEMS'][] = $list[$id];
		}
	}
} else {
	$_['PEOPLE_LIST_ITEMS'] = $list;
}
