<?php

$_['pages'] = get_db_data('SELECT alias FROM data_pages WHERE site_id = "' . $sitedata['id'] . '" and search_flag = 1');
$prepend = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

header('Content-Type: text/xml');
exit($prepend . $htmlmin->minify($twig->render('sitemap.twig', $_)) . '</urlset>');
