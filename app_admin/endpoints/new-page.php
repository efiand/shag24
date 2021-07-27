<?php

$page = trim($payload['newPage']);
if ($page) {
	if (strpos($page, '/') !== 0) {
		$page = '/' . $page;
	}
	$get_id_query = 'SELECT DISTINCT id FROM data_pages WHERE alias = "' . $page . '" and site_id = ' . $payload['currentSite'];
	if (!get_db_data($get_id_query)) {
		set_db_data('INSERT INTO data_pages (alias, site_id) VALUES ("' . $page . '", ' . $payload['currentSite'] . ')');
		set_db_data('INSERT INTO data_fragments (sort_order, alias, page_id, value, description, type, is_published)
			VALUES (1000, "CONTENT", (' . $get_id_query . '), "", "Содержимое страницы", "section", 1)');
	}
}
