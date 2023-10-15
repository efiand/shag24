<?php

$place = $payload['place'];

$queryBody = 'SET alias = "' . prepare_post_data($place['alias'])
	. '", title = "' . prepare_post_data($place['title']) . '"';

if ($place['country']) {
	$queryBody .= ', country = "' . prepare_post_data($place['country']) . '"';
} else {
	$queryBody .= ', country = NULL';
}

if ((int) $place['id'] > 0) {
	$query = 'UPDATE data_places ' . $queryBody . ' WHERE id = ' . $place['id'];
} else {
	$query = 'INSERT INTO data_places ' . $queryBody;
}

set_db_data($query);
