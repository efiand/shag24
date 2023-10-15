<?php

$event_type = $payload['eventType'];

$queryBody = 'SET title = "' . prepare_post_data($event_type['title']) . '"';

if ((int) $event_type['id'] > 0) {
	$query = 'UPDATE data_event_types ' . $queryBody . ' WHERE id = ' . $event_type['id'];
} else {
	$query = 'INSERT INTO data_event_types ' . $queryBody . ', adminzone = "' . $adminzone . '"';
}

set_db_data($query);
