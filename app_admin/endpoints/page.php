<?php

if ($payload['getPages']) {
	$_['pages'] = get_db_data('SELECT
		p.id,
		p.alias,
		s.alias as site_alias
	FROM data_pages p JOIN data_sites s ON p.site_id = s.id
	WHERE s.adminzone = "' . $adminzone . '" ORDER BY s.alias, p.alias');
	$_['sites'] = get_db_data('SELECT id, alias FROM data_sites WHERE adminzone = "' . $adminzone . '"');

	$_['forms'] = get_db_data('SELECT id, topic, description, opener_title, payment_target, attach FROM data_forms WHERE adminzone = "' . $adminzone . '"', 'id');
	$_['events'] = get_db_data('SELECT id, title, datetime_start FROM data_events WHERE adminzone = "' . $adminzone . '" ORDER BY id', 'id');
	$_['people'] = get_db_data('SELECT id, name FROM data_people WHERE adminzone = "' . $adminzone . '" ORDER BY id', 'id');
} else {
	$_['page'] = get_db_data('SELECT
		p.id,
		p.alias,
		p.site_id,
		s.alias as site_alias,
		p.title,
		p.description,
		p.receivers,
		p.search_flag
	FROM data_pages p JOIN data_sites s ON p.site_id = s.id
	WHERE p.id = ' . $payload['pageId'] . ' AND s.adminzone = "' . $adminzone . '"', 'single');
	if ($_['page']['id']) {
		$_['page']['search_flag'] = !!$_['page']['search_flag'];
		$fragments = get_db_data('SELECT
			id,
			sort_order,
			alias,
			page_id,
			(SELECT ds.alias FROM data_sites ds WHERE ds.id = (SELECT dp.site_id FROM data_pages dp WHERE dp.id = df.page_id)) AS site,
			value,
			description,
			type,
			is_published
		FROM data_fragments df WHERE page_id = ' . $payload['pageId'] . ' ORDER by sort_order');

		foreach ($fragments as $id => &$fragment) {
			$fragment['is_published'] = (bool) $fragment['is_published'];
			$linked_with = get_db_data('SELECT DISTINCT id FROM data_fragments WHERE type = "link" AND value = "' . $fragment['id'] . '"');
			$fragment['deletable'] = !count($linked_with);
		}
		$_['fragments'] = $fragments;
	}
}
