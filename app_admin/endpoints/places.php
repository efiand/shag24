<?php

$places = get_db_data('SELECT * FROM data_places ORDER by title');

foreach($places as $place) {
	$_['places'][] = $place;
}
