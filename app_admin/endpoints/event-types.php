<?php

$event_types = get_db_data('SELECT * FROM data_event_types WHERE adminzone = "' . $adminzone . '" ORDER by title');

$_['eventTypes'] = [];
foreach($event_types as $event_type) {
	$_['eventTypes'][] = $event_type;
}
