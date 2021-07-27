<?php

// Список страниц для фильтра по ним
$_['pages'] = get_db_data('SELECT
	p.id,
	p.alias,
	s.alias as site_alias
FROM data_pages p JOIN data_sites s ON p.site_id = s.id
WHERE s.adminzone = "' . $adminzone . '" AND p.id IN (SELECT page_id FROM data_fragments WHERE type = "ticket-button")
ORDER BY s.alias, p.alias');

$query = 'SELECT * FROM data_forms df WHERE adminzone = "' . $adminzone . '"';
if ($payload['filter']) {
	$filter = trim($payload['filter']);
	$query .= ' AND (';
	if (preg_match('/^[\d, ]*\d$/', $filter)) {
		$query .= 'id IN (' . $filter . ')';
	} else {
		$query .= 'topic LIKE "%' . $filter . '%" OR description LIKE "%' . $filter . '%"';
	}
	$query .= ')';
}

if ($payload['pageId']) {
	$page_id = (int) $payload['pageId'];
	$query .= ' AND id IN (SELECT value FROM data_fragments WHERE page_id = ' . $page_id . ' AND value = df.id)';
}

if ($payload['paymentsOnly']) {
	$query .= ' AND price > 0';
}

$query .= ' ORDER BY id';

$_['forms'] = get_db_data($query);
