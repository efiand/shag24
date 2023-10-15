<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$_['error'] = '';
	foreach ($payload['parameters'] as $parameter) {
		if (!set_db_data('UPDATE parameters set value = "' . $parameter['value'] . '" WHERE name = "' . $parameter['name'] . '"')) {
			$_['error'] .= ' Ошибка сохранения параметра ' . $parameter['description'] . '.';
		}
	}
} else {
	$_['parameters'] = get_db_data('SELECT * FROM parameters WHERE adminzone = "' . $adminzone . '" or adminzone IS NULL');
}

$_['method'] = $_SERVER['REQUEST_METHOD'];
