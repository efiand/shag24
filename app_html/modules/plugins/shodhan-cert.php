<?php
// Фрагмент, отвечающий за вывод списка сертифицированных (II уровня) инструкторов Шодхан

$_['SHODHAN_CERT'] = get_db_data('SELECT
	pe.id,
	name,
	dp.title AS place,
	dp.country AS country,
	url,
	shodhan_flag
FROM data_people pe LEFT JOIN data_places dp ON dp.id = pe.place_id
WHERE pe.adminzone = "khara" AND shodhan_cert_flag = 1
ORDER BY name');

foreach ($_['SHODHAN_CERT'] as &$instructor) {
	if ($instructor['shodhan_flag']) {
		$instructor['url'] = '/shodhan/instructors?id=' . $instructor['id'];
	}
}
