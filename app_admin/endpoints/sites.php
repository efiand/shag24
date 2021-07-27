<?php

$sites = get_db_data('SELECT * FROM data_sites WHERE adminzone = "' . $adminzone . '"');

foreach($sites as $site) {
	$site['has_place_chooser'] = !!$site['has_place_chooser'];
	$site['has_master_chooser'] = !!$site['has_master_chooser'];
	$site['has_place_events_page'] = !!$site['has_place_events_page'];
	$_['sites'][] = $site;
}
