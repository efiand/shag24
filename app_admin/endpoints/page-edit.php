<?php

$page = $payload['pagedata'];

$query = 'UPDATE data_pages SET';
if ($page['alias']) {
	$query .= ' alias = "' . trim($page['alias']) . '",';
}
if ($page['title']) {
	$query .= ' title = "' . prepare_post_data($page['title']) . '",';
} else {
	$query .= ' title = NULL,';
}
if ($page['description']) {
	$query .= ' description = "' . prepare_post_data($page['description']) . '",' ;
} else {
	$query .= ' description = NULL,';
}
if ($page['receivers']) {
	$query .= ' receivers = "' . trim($page['receivers']) . '",';
} else {
	$query .= ' receivers = NULL,';
}
$query .= ' site_id = ' . $page['site_id'] . ', search_flag = ' . (int) $page['search_flag'] . '
WHERE id = ' . $page['id'];

if (set_db_data($query)) {
	$_['site_alias'] = get_db_data('SELECT alias FROM data_sites WHERE id = ' . $page['site_id'], 'single')['alias'];
};
