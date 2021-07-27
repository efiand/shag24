<?php

// Особая функциональность асинхронного получения списка людей

header('Content-Type: application/json');
$people_nums = json_decode(file_get_contents('php://input'), true);

if (is_array($people_nums)) {
	$query = 'SELECT id, name, description FROM data_people WHERE id IN(' . implode(',', $people_nums) . ')';
	$res['people'] = get_db_data($query);
} else {
	http_response_code(400);
	$res['people'] = [];
	$res['error'] = 'Не передан список идентификаторов людей!';
}

exit(json_encode($res, JSON_UNESCAPED_UNICODE));
