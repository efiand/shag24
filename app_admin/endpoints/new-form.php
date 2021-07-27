<?php

$topic = prepare_post_data($payload['newTopic']);
if ($topic) {
	set_db_data('INSERT INTO data_forms SET topic = "' . $topic . '", adminzone = "' . $adminzone . '"');
}
