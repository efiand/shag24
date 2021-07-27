<?php
// Микросервис экспорта в Excel/CSV

ini_set('memory_limit', '512M');

$root_path = '../../';
require_once $root_path . 'app_admin/core.php';

require_once $root_path . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader('./'), [
	'autoescape' => false,
	'debug' => $is_dev
]);

// Экспорт только по сайтам текущей админзоны
$adminzone_domain_aliases = get_db_data('SELECT alias FROM data_sites WHERE adminzone = "' . $adminzone . '"');
$site_query = '(';
foreach ($adminzone_domain_aliases as $i => $site) {
	if ($i) {
		$site_query .= ' OR ';
	}
	$site_query .= 'page LIKE "' . $site['alias'] . '%"';
}
$site_query .= ')';


$query = 'SELECT create_time, topic, page, name, city, phone, mail, instagram, message, promocode, order_id, payment_status FROM data_tickets WHERE '. $site_query;
if ($payload['ticketPage']) {
	$query .= ' AND page = "' . $payload['ticketPage'] . '"';
}
if ($payload['startDate']) {
	$query .= ' AND datediff(create_time, "' . $payload['startDate'] . '") >= 0';
}
if ($payload['endDate']) {
	$query .= ' AND datediff(create_time, "' . $payload['endDate'] . '") <= 0';
}
$query .= ' ORDER BY create_time desc';
if ($is_dev) {
	$_['query'] = $query;
}
$_['tickets'] = get_db_data($query);

$now = date('Y-m-d-H-i-s', $_SERVER['REQUEST_TIME']);
$data = $twig->render('template.twig', $_);
header('Content-Disposition: attachment; filename="tickets-' .  $now . '.' . $_GET['mode'] . '"');

if ($_GET['mode'] === 'xlsx') {
	$spreadsheet = new Spreadsheet();
	$spreadsheet -> setActiveSheetIndex(0);
	$sheet = $spreadsheet->getActiveSheet();

	$list = explode("\n", trim($data));
	$line = 0;
	foreach ($list as $item) {
		$line ++;
		$str_list = explode('";"', substr($item, 1, -1));

		foreach ($str_list as $col => $row) {
			$sheet -> setCellValueByColumnAndRow($col + 1, $line, $row);
		}
	}

	header('Content-type: application/vnd.ms-excel');
	$writer = new Xlsx($spreadsheet);
	$writer->save('php://output');
	exit;
} else {
	header('Content-Type: text/csv');
	exit(chr(239) . chr(187) . chr(191) . $data);
}
