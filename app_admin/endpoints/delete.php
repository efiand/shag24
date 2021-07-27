<?php

$_['status'] = false;

if (isset($payload['dbtable'])) {
	$value = $payload['colvalue'];
	if ($payload['stringFlag']) {
		$value = '"' . $value . '"';
	}

	if (set_db_data('DELETE from ' . $payload['dbtable'] . ' where ' . $payload['colname'] . ' = ' . $value)) {
		if ($payload['dbtable'] === 'data_pages') {
			set_db_data('DELETE from data_fragments where page_id = ' . $value);
		} else if ($payload['dbtable'] === 'data_people' && file_exists($avatar_dir . $value . '.jpg')) {
			unlink($avatar_dir . $value . '.jpg');
		}
		$_['status'] = true;
	}
}
