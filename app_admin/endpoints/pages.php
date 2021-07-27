<?php

$_['sites'] = get_db_data('SELECT id, alias FROM data_sites WHERE adminzone = "' . $adminzone . '"');

if (!$payload['currentSite']) {
	$payload['currentSite'] = $_['sites'][0]['id'];
}

$_['pages'] = get_db_data('SELECT * FROM data_pages WHERE site_id = ' . $payload['currentSite'] . ' ORDER BY alias');
