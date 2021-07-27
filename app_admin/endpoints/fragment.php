<?php

$fragment = get_db_data('SELECT
	f.id,
	f.sort_order,
	f.alias,
	f.page_id,
	s.alias AS site,
	f.value,
	f.description,
	f.type,
	f.is_published
FROM data_fragments f JOIN data_pages p ON f.page_id = p.id JOIN data_sites s ON p.site_id = s.id
WHERE f.id = ' . $payload['fragmentId'] . ' AND s.adminzone = "' . $adminzone . '"', 'single');
$_['fragment'] = [];

if (isset($fragment['id'])) {
	$fragment['is_published'] = (bool) $fragment['is_published'];
	$_['forms'] = get_db_data('SELECT id, topic, description, opener_title, payment_target, attach FROM data_forms WHERE adminzone = "' . $adminzone . '"', 'id');
	$_['events'] = get_db_data('SELECT id, title, datetime_start FROM data_events WHERE adminzone = "' . $adminzone . '" ORDER BY id', 'id');
	$_['people'] = get_db_data('SELECT id, name FROM data_people WHERE adminzone = "' . $adminzone . '" ORDER BY id', 'id');
	$_['fragment'] = $fragment;
	$linked_with = get_db_data('SELECT DISTINCT id FROM data_fragments WHERE type = "link" AND value = "' . $fragment['id'] . '"');
	$_['fragment']['deletable'] = !count($linked_with);
}
