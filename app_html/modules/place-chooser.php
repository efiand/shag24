<?php

// Города для выбора на сайте
if ($_['EVENTS_CITIES']) {
	$_['PLACES'] = $_['EVENTS_CITIES'];
} else {
	$_['PLACES'] = get_db_data('SELECT * FROM data_places WHERE alias in (SELECT SUBSTRING(alias, 2) AS page FROM data_pages WHERE site_id = ' . $sitedata['id'] . ') ORDER BY title');
}

// Текущий город
if ($_['EVENTS_CITY']) {
	$_['CURRENT_PLACE'] = $_['EVENTS_CITY'];
} else {
	$_['CURRENT_PLACE'] = get_db_data('SELECT * FROM data_places WHERE alias = "' . $_['PAGE_ALIAS'] . '"', 'single');
}

if ($_['CURRENT_PLACE']) {
	$_['PLACE_CHOOSER_TITLE'] = '<span class="chooser__place-text hidden-mobile">Место проведения: </span>' . $_['CURRENT_PLACE']['title'];

	if ($_['CURRENT_PLACE']['country']) {
		$_['PLACE_CHOOSER_TITLE'] .= ', ' . $_['CURRENT_PLACE']['country'];
	}
} else {
	$_['PLACE_CHOOSER_TITLE'] = 'Место проведения';
}

if ($_['EVENTS_CITIES'] && !$_['PAGE_ALIAS']) {
	$_['INDEX_CHOOSER_FLAG'] = true;
}
