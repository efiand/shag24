<?php
$page_query = ' (page' . ($adminzone === 'khara' ? '' : ' NOT') . ' LIKE "%hara%")';

$query = 'SELECT id, create_time, topic, page, name, city, phone, email as mail, instagram, message, promocode, order_id, payment_status FROM data_tickets WHERE'
	. $page_query;
if ($payload['keywords']) {
	$keywords = trim($payload['keywords']);
	$query .= ' AND (name LIKE "%' . $keywords . '%" OR topic LIKE "%' . $keywords . '%" OR city LIKE "%' . $keywords . '%" OR phone LIKE "%' . $keywords . '%" OR email LIKE "%' . $keywords . '%" OR instagram LIKE "%' . $keywords . '%" OR message LIKE "%' . $keywords . '%" OR promocode LIKE "%' . $keywords . '%" OR order_id LIKE "%' . $keywords . '%")';
}
if ($payload['ticketPage']) {
	$query .= ' AND page = "' . $payload['ticketPage'] . '"';
}
if ($payload['startDate']) {
	$query .= ' AND datediff(create_time, "' . $payload['startDate'] . '") >= 0';
}
if ($payload['endDate']) {
	$query .= ' AND datediff(create_time, "' . $payload['endDate'] . '") <= 0';
}
if ($payload['paymentStatus']) {
	if ($payload['paymentStatus'] === '-') {
		$query .= ' AND payment_status IS NULL';
	} else if ($payload['paymentStatus'] === 'no') {
		$query .= ' AND payment_status IS NOT NULL AND payment_status <> "Подтвержден"';
	} else if ($payload['paymentStatus'] === 'yes') {
		$query .= ' AND payment_status = "Подтвержден"';
	}
}
$query .= ' ORDER BY create_time desc';
if (isset($payload['limit'])) {
	$query .= ' limit ' . $payload['limit'];
}
$_['tickets'] = get_db_data($query);

$payment_statuses = get_db_data('SELECT status FROM data_payment_status');
$_['payment_statuses'] = [];
foreach ($payment_statuses as $status) {
	$_['payment_statuses'][] = $status['status'];
}

$pages = get_db_data('SELECT DISTINCT page FROM data_tickets WHERE' . $page_query . ' ORDER BY page');
$_['pages'] = [];
foreach ($pages as $page) {
	$_['pages'][] = $page['page'];
}
