<?php
$query = 'SELECT
	pe.id,
	name,
	email,
	url,
	tel,
	description,
	shodhan_cert_flag,
	dp.title AS place,
	dp.country AS country
FROM data_people pe LEFT JOIN data_places dp ON dp.id = pe.place_id
WHERE pe.adminzone = "khara" AND pe.shodhan_flag = 1';
if (isset($_GET['id'])) {
	$query .= ' AND pe.id = ' . $_GET['id'];
}
$query .= ' ORDER BY name';

$instructors = get_db_data($query, 'id');
if (!count($instructors)) {
	header('Location: /shodhan/instructors');
	exit;
}

foreach ($instructors as $id => $instructor) {
	if (file_exists('media/people/' . $id . '.jpg')) {
		$instructors[$id]['img'] = '//khara.ru/media/people/' . $id . '.jpg';
	}
	if (isset($instructors[$id]['url'])) {
		$instructors[$id]['url_text'] = preg_replace('/^http(s)?:\/\//', '', $instructors[$id]['url']);
	}
}

$_['SHODHAN_INSTRUCTORS'] = $instructors;
