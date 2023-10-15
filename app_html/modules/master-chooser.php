<?php

// Получаем список мастеров, у которых есть актуальные мероприятия
$_['MASTERS'] = get_db_data('SELECT people_id AS id, instructor FROM view_events WHERE instructor IS NOT NULL AND adminzone = "'
	.$sitedata['adminzone']
	. '" AND is_published = 1 AND datediff(datetime_end_str, current_timestamp) >= 0 GROUP BY people_id ORDER BY instructor', 'id');

if ($_['CURRENT_MASTER']) {
	$_['CHOOSER_TITLE'] = '<span class="hidden-mobile">Мастер: </span>' . $_['CURRENT_MASTER'];
} else {
	$_['CHOOSER_TITLE'] = 'Мастер';
}
