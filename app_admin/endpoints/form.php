<?php

$_['pages'] = get_db_data('SELECT
	p.id,
	p.alias,
	s.alias as site_alias
FROM data_pages p JOIN data_sites s ON p.site_id = s.id
WHERE s.adminzone = "' . $adminzone . '" ORDER BY s.alias, p.alias');
$_['forms'] = get_db_data('SELECT id, topic, description FROM data_forms WHERE adminzone = "' . $adminzone . '" ORDER BY id');

$form = get_db_data('SELECT * FROM data_forms WHERE id = ' . $payload['id'] . ' AND adminzone = "' . $adminzone . '"', 'single');
$form['has_city'] = (bool) $form['has_city'];
$form['has_instagram'] = (bool) $form['has_instagram'];
$form['has_promocode'] = (bool) $form['has_promocode'];
$form['require_message'] = (bool) $form['require_message'];
$form['notify_uncompl_flag'] = (bool) $form['notify_uncompl_flag'];
$_['form'] = $form;
