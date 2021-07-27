<?php

// Страница мастеров - автособираемая

if (!$_['HAS_MASTER_CHOOSER']) {
	http_response_code(404);
	header('Location: /error404');
	exit;
}

preg_match('/^\/master\/?(\d+)$/', $page, $matches);

$master = get_db_data('SELECT * FROM data_people WHERE id = '
	. ($matches[1] ?? null) . ' AND adminzone = "' . $_['ADMINZONE'] . '"', 'single');

if (!$master['id']) {
	http_response_code(404);
	header('Location: /error404');
	exit;
}

if (file_exists('media/people/' . $master['id'] . '.jpg')) {
	$master['img'] = '//' . $_['HOST'] . '/media/people/' . $master['id'] . '.jpg';
}

// внедряем принудительно плагин расписания
$_['SCHEDULE_FILTER'] = 'people_id = ' . $master['id'];
require_once '../app_html/modules/plugins/schedule.php';
$_['SCHEDULE'] =  $twig->render('plugins/schedule.twig', $_);

$_['VIDEOS'] = get_db_data('SELECT * FROM people_video WHERE people_id = '
	. $master['id'] . ' AND url != "" ORDER BY sort_order');

$_['HEAD_TITLE'] .= ' : ' . $master['name'];

$_['MASTER'] = $master;
$_['CURRENT_MASTER'] = $master['name'];
$_['AUTOTEMPLATE'] = file_get_contents('../app_html/templates/fragments/master.twig');
