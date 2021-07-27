<?php

$is_dev = file_exists('../package.json') || isset($_GET['debug']) || false !== strpos($_SERVER['HTTP_HOST'], 'test.');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', (int) $is_dev);
ini_set('display_startup_errors', (int) $is_dev);

$env = json_decode(file_get_contents('../env.json'), true);
try {
	$db = mysqli_connect($env['db_ip'], $env['db_user'], $env['db_pass'], $env['db_name']);
	if (!$db) {
		throw new Exception('Ошибка подключения к базе данных.');
	}
} catch (Exception $e) {
	http_response_code(500);
	exit($is_dev ? $e : $e->getMessage());
}
mysqli_set_charset($db, 'utf8');

$site = strtolower(str_replace(['www.', 'test.', '-ru', '.ru'], '', $_SERVER['HTTP_HOST']));
$page = strtolower(str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
$page_alias = substr($page, 1);

// Загрузка сторонних библиотек через composer
require_once '../vendor/autoload.php';
// Собственная библиотека (использует сторонние)
require_once '../app_html/utils.php';

// Данные сайта
$sitedata = get_db_data('SELECT * FROM data_sites WHERE alias = "' . $site . '"', 'single');

$events_cities = [];
$events_city = [];
if ($sitedata['has_place_events_page']) {
	// Получаем список городов, в которых есть мероприятия
	$events_cities = get_db_data('SELECT DISTINCT place_alias as alias, place as title, country
		FROM bhx20922_data.view_events WHERE adminzone = "' . $sitedata['adminzone']
		. '" AND datediff(datetime_end_str, current_timestamp) >= 0 and is_published = 1 ORDER BY place', 'alias');

	// Проверяем, есть ли не найденная в базе страница в числе таких городов
	if (isset($events_cities[$page_alias])) {
		$events_city = $events_cities[$page_alias];
		$page = '/';

		// Данные главной страницы как основы страницы города мероприятий
		$pagedata = get_db_data('SELECT * FROM data_pages WHERE alias = "/" and site_id = ' . $sitedata['id'], 'single');
	}
}

// Данные страницы
if (!isset($pagedata)) {
	$pagedata = get_db_data('SELECT * FROM data_pages WHERE alias = "' . $page . '" and site_id = ' . $sitedata['id'], 'single');
}

// Массив данных для шаблонизатора
// Эти параметры можно переопределить одноименными фрагментами страницы
$_ = [
	'IS_DEV' => $is_dev,
	'PAGE' => $page,
	'PAGE_ALIAS' => $page_alias,
	'SITE' => $sitedata['alias'],
	'SITE_ID' => $sitedata['id'],
	'HOST' => $_SERVER['HTTP_HOST'],
	'URL' => $_SERVER['HTTP_HOST'] . $page,
	'ADMINZONE' => $sitedata['adminzone'],
	'GET' => $_GET,
	'SITE_TEL' => $sitedata['tel'],
	'SITE_MAIL' => $sitedata['mail'],
	'SITE_HEAD_CODE' => $sitedata['head_code'],
	'SITE_FOOT_CODE' => $sitedata['foot_code'],
	'SOCIALS' => json_decode($sitedata['socials'], true),
	'SITE_TITLE' => $sitedata['title'],
	'SITE_DESCRIPTION' => $pagedata['description'] ? $pagedata['description'] : $sitedata['description'],
	'SITE_MENU' => json_decode($sitedata['menu'], true),
	'OG_IMAGE' => $sitedata['og_image'] ? json_decode($sitedata['og_image']) : [],
	'SENDER' => $sitedata['sender'],
	'SENDER_NAME' => $sitedata['sender_name'],
	'PAGE_TITLE' => $pagedata['title'],
	'RECEIVERS' => $pagedata['receivers'],
	'HEAD_TITLE' => ($pagedata['title'] ? $pagedata['title'] . ' : ' : '') . $sitedata['title'],
	'SHOP' => $sitedata['shop'],
	'HAS_PLACE_EVENTS_PAGE' => $sitedata['has_place_events_page'],
	'EVENTS_CITIES' => $events_cities,
	'EVENTS_CITY' => $events_city,
	'HAS_MASTER_CHOOSER' => $sitedata['has_master_chooser'],
	'MASTER_CHOOSER' => '',
	'CURRENT_MASTER' => '',
	'VERSION' => get_db_data('SELECT value FROM parameters WHERE name = "version"', 'single')['value']
];
if ($events_city) {
	$_['HEAD_TITLE'] = $events_city['title'] . ' : ' . $sitedata['title'];
}

// Поддержка выбора городов
if ($sitedata['has_place_chooser']) {
	require_once '../app_html/modules/place-chooser.php';
	$_['PLACE_CHOOSER'] =  $twig->render('place-chooser.twig', $_);
}

// Автогенерируемый (из фрагментов) контент при отсутствии такового у страницы
$autotemplate = '';

// Если в пределах сайта страница не создана вручную (у нее высший приоритет) - обрабатываем роуты
if ($pagedata['id']) {
	// Фрагменты страницы
	$fragments = get_db_data('SELECT alias, value, type FROM data_fragments WHERE page_id = '
		. $pagedata['id'] . ' AND is_published = 1 ORDER by sort_order', 'alias');

	// Формы, привязанные к странице
	$form_numbers = [];
	foreach ($fragments as $fragment) {
		if ($fragment['type'] === 'ticket-button') {
			$form_numbers[] = $fragment['value'];
		}
	}
	$forms = count($form_numbers) ? get_db_data('SELECT * FROM data_forms WHERE id in (' . implode(',', $form_numbers) . ')', 'id') : [];
	foreach ($forms as &$form) {
		$form['topic'] = str_replace('"', '&quot;', $form['topic']);
		if (isset($form['choose_field'])) {
			$form['choose_field'] = json_decode($form['choose_field'], true);
		}
		// Нужно ли добавлять на страницу платежные скрипты
		if ($form['payment_target']) {
			$_['HAS_PAYMENT'] = true;
		}
	}
	$_['FORMS'] = $forms;

	// Обработка фрагментов в зависимости от формата данных
	$places_with_fragments = [];
	foreach ($fragments as $alias => $param) {
		if ($param['type'] === 'link') {
			$param = get_db_data('SELECT * FROM data_fragments WHERE id = ' . $param['value'], 'single');
		}
		$type = $param['type'] ?? 'text';
		$value = $param['value'] ?? '';
		if (substr($alias, 0, 13) === 'PLACE_SWITCH_') {
			$places_with_fragments[] = strtolower(substr($alias, 13));
		}
		if ($value) {
			if ($type === 'json') {
				$json = json_decode($value, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					// проверка на JSON пройдена - сохраняем в виде массива данных
					$_[$alias] = $json;
				}
			} else if ($type === 'img') {
				// Фрагмент является именем файла картинки из данной админзоны
				$_[$alias] = $twig->render('fragments/img.twig', [
					'host' => $_['HOST'],
					'adminzone' => $_['ADMINZONE'],
					'file' => $value
				]);
			} else if ($type === 'youtube') {
				// Фрагмент является ссылкой на встраивание Youtube-видеоролика
				$_[$alias] = $twig->render('fragments/media.twig', [
					'url' => $value
				]);
			} else if ($type === 'ticket-button') {
				// Фрагмент является кнопкой открытия формы заявки (значение - номер формы)
				if (isset($forms[$value])) {
					// Форма должна быть привязана к странице
					$_[$alias] = $twig->render('fragments/ticket-button.twig', [
						'form' => $forms[$value]
					]);
				}
			} else if ($type === 'person') {
				$_[$alias] =  get_db_data('SELECT * FROM data_people WHERE id = ' . $value, 'single');
			} else if ($type === 'event') {
				$_[$alias] =  get_db_data('SELECT * FROM view_events WHERE id = ' . $value, 'single');
			} else if ($type === 'tel-link') {
				// Фрагмент является блоком ссылок на телефон, viber, whatsapp, telegram
				$_[$alias] = $twig->render('fragments/tel-links.twig', [
					'tel' => $value
				]);
			} else if ($type === 'section') {
				// Фрагмент является центрированным блоком контента с якорем
				$_[$alias] = $twig->render('fragments/section.twig', [
					'id' => strtolower($alias),
					'content' => $twig->createTemplate($value)->render($_)
				]);
			} else if (false !== strpos($value, '{{')) {
				// Проверка на twig пройдена - рендерим
				$_[$alias] = $twig->createTemplate($value)->render($_);
			} else {
				// Ничего не пройдено - оставляем как строку
				$_[$alias] = $value;
			}

			// Автогенерируемый контент
			if (array_search($type, ['visual-auto', 'html-auto', 'section'])) {
				$autotemplate .= '{{' . $alias . '}}';
			}

			if ($alias === 'PLACE_FRAGMENT_SWITCHER') {
				$autotemplate .= '{{PLACE_FRAGMENT_SWITCHER_TPL}}';
			}
		} else if ($type === 'plugin') {
			// Фрагмент является плагином
			$fragment = str_replace('_', '-', strtolower($alias));
			if (file_exists('../app_html/templates/plugins/' . $fragment . '.twig')) {
				if (file_exists($fragment_model = '../app_html/modules/plugins/' . $fragment . '.php')) {
					require_once $fragment_model;
				}
				$_[$alias] =  $twig->render('plugins/' . $fragment . '.twig', $_);
				$autotemplate .= '{{' . $alias . '}}';
			}
		} else {
			// Пустая строка иногда нужна для переопределения
			$_[$alias] = $value;
		}
	}

	// Переключатель фрагментов мест
	if (isset($_['PLACE_FRAGMENT_SWITCHER']) && count($places_with_fragments)) {
		$_['PLACE_FRAGMENT_SWITCHER']['places'] = get_db_data('SELECT alias, title FROM data_places WHERE alias IN("'
			. implode('","', $places_with_fragments) . '") ORDER BY title');
		$_['PLACE_FRAGMENT_SWITCHER_TPL'] = $twig->render('place-fragment-switcher.twig', $_);
		if (isset($_['PLACE_FRAGMENT_SWITCHER']['forms'])) {
			foreach ($_['PLACE_FRAGMENT_SWITCHER']['forms'] as &$form) {
				$_['FORMS'][$form]['switch_place'] = 1;
			}
		}
	}
} else {
	preg_match('/^\/[^\/]+/', $page, $app_name);
	if (isset($app_name[0])) {
		$route_app = '../app_html/routes' . $app_name[0] . '.php';

		if (file_exists($route_app)) {
			require_once $route_app;
		} else {
			http_response_code(404);
			header('Location: /error404');
			exit;
		}
	}
}

// Редирект
if (isset($_['REDIRECT']) && $_['REDIRECT']) {
	http_response_code(301);
	header('Location: ' . $_['REDIRECT']);
	exit;
}

// Модель сайта
if (file_exists($site_model = '../app_html/modules/sites/' . $site . '.php')) {
	require_once $site_model;
}

if ($_['HAS_MASTER_CHOOSER']) {
	require_once '../app_html/modules/master-chooser.php';
	$_['MASTER_CHOOSER'] =  $twig->render('master-chooser.twig', $_);
}

$template = $_['AUTOTEMPLATE'] ?? $autotemplate;
if ($template) {
	// Рендеринг основной части страницы (между шапкой и подвалом)
	$_['TEMPLATE'] = $twig->createTemplate($template)->render($_);
}
// Рендеринг и вывод общего шаблона страницы
exit($htmlmin->minify($twig->render('sites/' . $site . '.twig', $_)));
