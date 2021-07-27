<?php

header('Content-Type: text/plain');

// Не показываем на тестовом полигоне
if (0 === strpos($_['HOST'], 'test.')) {
	exit("Disallow: /\n");
}

// Добавляем к запрещенным страницы, которые не индексируются
$disallow_pages = get_db_data('SELECT alias FROM data_pages WHERE site_id = "' . $sitedata['id'] . '" and search_flag = 0', 'alias');

$_['disallow'] = array_merge(['/?', '/tickets'], array_keys($disallow_pages));
$_['agents'] = ['Yandex', '*'];

exit($twig->render('robots.twig', $_));
