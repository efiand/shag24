<?php

// возвращает результат запроса к БД
function get_db_data($query, $mode = '') {
	global $_, $db, $is_dev;
	$result = [];
	$raw = mysqli_query($db, $query);
	if (!$raw) {
		$_['error'] = $is_dev ? mysqli_error($db) : 'Ошибка получения данных';
	} else if ($mode === 'single') {
		// если нужна только одна запись - необходимости во вложенном массиве нет
		$result = mysqli_fetch_assoc($raw);
	} else {
		while ($data = mysqli_fetch_assoc($raw)) {
			if (isset($data['alias']) && $mode === 'alias') {
				$result[$data['alias']] = $data;
			} else if (isset($data['id']) && $mode === 'id') {
				$result[$data['id']] = $data;
			} else {
				$result[] = $data;
			}
		}
	}
	return $result;
}

// замена кавычек на условный символ при сохранении в БД
function prepare_post_data($string) {
	return trim(mysqli_real_escape_string($GLOBALS['db'], $string));
}

// Добавляет данные в БД
function set_db_data($query) {
	global $_, $db, $is_dev;
	$result = mysqli_query($db, $query);
	if (!$result) {
		$_['error'] = $is_dev ? mysqli_error($db) : 'Ошибка отправки данных. Обновите страницу и попробуйте снова.';
	}
	return (bool) $result;
}
