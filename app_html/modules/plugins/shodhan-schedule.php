<?php
// Фрагмент, отвечающий за вывод расписания Шодхан

$_['SCHEDULE'] = get_db_data('SELECT * FROM view_events
	WHERE adminzone = "khara" AND type_id < 4 AND datediff(datetime_end_str, current_timestamp) >= 0');
