<?php

$days_delim = '&hairsp;–&hairsp;';
$months_delim = ' – ';
$months_genitive = [
	'января',
	'февраля',
	'марта',
	'апреля',
	'мая',
	'июня',
	'июля',
	'августа',
	'сентября',
	'октября',
	'ноября',
	'декабря'
];

// Форматирует номер телефона для ссылки
function format_tel($tel) {
	return preg_replace('/[^0-9+]/', '', $tel);
}

// Диапазон дат с учетом различий в месяце и годе
function get_date_range($start, $end = null, $year_flag = false) {
	$day_start = date('j', $start);
	$month_start = ' ' . $GLOBALS['months_genitive'][(int) date('m', $start) - 1];
	$year_start = ($year_flag) ? ' ' . date('Y', $start) : '';

	if ($end) {
		$day_end = date('j', $end);
	}

	if (!$end || $day_start === $day_end) {
		return $day_start . $month_start . $year_start;
	}

	$month_end = ' ' . $GLOBALS['months_genitive'][(int) date('m', $end) - 1];
	$year_end = ($year_flag) ? ' ' . date('Y', $end) : '';

	if ($month_end !== $month_start) {
		if ($year_end !== $year_start) {
			return $day_start . $month_start . $year_start . $GLOBALS['months_delim'] . $day_end . $month_end . $year_end;
		}
		return $day_start . $month_start . $GLOBALS['months_delim'] . $day_end . $month_end . $year_start;
	}
	return $day_start . $GLOBALS['days_delim'] . $day_end . $month_start . $year_start;
}

// возвращает результат запроса к БД
function get_db_data($query, $mode = '') {
	$result = [];
	$raw = mysqli_query($GLOBALS['db'], $query);
	if (!$raw) {
		exit($GLOBALS['is_dev'] ? mysqli_error($GLOBALS['db']) : 'Ошибка получения данных');
	} else if ($mode === 'single') {
		// если нужна только одна запись - необходимости во вложенном массиве нет
		$result = mysqli_fetch_assoc($raw);
	} else {
		while ($data = mysqli_fetch_assoc($raw)) {
			if (isset($data['alias']) && $mode === 'alias') {
				$result[$data['alias']] = $data;
			} else if (isset($data['id']) && $mode === 'id') {
				$result[$data['id']] = $data;
			} else {
				$result[] = $data;
			}
		}
	}
	return $result;
}

// Диапазон дат со временем
function get_time_range($start, $end = null, $time_flag = false, $online_flag = false) {
	$time_start = date('H:i', $start);
	$day_start = date('j', $start);
	$month_start = ' ' . $GLOBALS['months_genitive'][(int) date('m', $start) - 1];
	$time_start_def = ($time_flag && $time_start !== '00:00') ? $time_start : '';
	if ($online_flag && $time_start_def) {
		$time_start_def .= '&hairsp;МСК';
	}
	$time_start_seq = ($time_start_def ? ', ' . $time_start_def : '');

	if (!$end || $start === $end) {
		return $day_start . $month_start . $time_start_seq;
	}

	if ($end) {
		$time_end = date('H:i', $end);
		$day_end = date('j', $end);
		$month_end = ' ' . $GLOBALS['months_genitive'][(int) date('m', $end) - 1];
		$time_end_def = ($time_flag && $time_end !== '00:00') ? $time_end : '';
		if ($online_flag && $time_end_def) {
			$time_end_def .= '&hairsp;МСК';
		}
		$time_end_seq = ($time_end_def ? ', ' . $time_end_def : '');
	}

	if (!$time_flag && $month_start === $month_end) {
		return $day_start . $GLOBALS['days_delim'] . $day_end . ' ' . $month_start;
	}

	if ($day_start === $day_end) {
		return $day_start . $month_start . $time_start_seq . $GLOBALS['days_delim'] . $time_end_def;
	}

	return $day_start . $month_start . $time_start_seq . $GLOBALS['months_delim'] . $day_end . $month_end . $time_end_seq;
}

// Форматирует с сохранением только цифр
function numberize($str) {
	return preg_replace('/[^0-9]/', '', $str);
}

// замена кавычек на условный символ при сохранении в БД
function prepare_post_data($string) {
	return trim(mysqli_real_escape_string($GLOBALS['db'], $string));
}

// убираем висячие предлоги
function remove_trailing_prepos($str) {
	return preg_replace('/( | |&nbsp;|\(|>){1}([№а-уА-У]{1}|\d+) /u', '$1$2$3 ', $str);
}

// Добавляет данные в БД
function set_db_data($query) {
	global $db, $res, $is_dev;
	$result = mysqli_query($db, $query);
	if (!$result) {
		$error = $is_dev ? mysqli_error($db) : 'Ошибка отправки данных. Обновите страницу и попробуйте снова.';
		if (!isset($res)) {
			exit($error);
		}
	}
	return (bool) $result;
}

// Возвращает строковое представление числа с отделением тысячных разрядов пробелом
function split_num_by_groups($num) {
	return preg_replace('/(\d)(?=(\d\d\d)+(?!\d))/', '$1 ', $num);
}

// Фильтры и окружение шаблонизатора (на основе utils)
class Twig_Extension extends \Twig\Extension\AbstractExtension {
	public function getFilters() {
		return [
			new \Twig\TwigFilter('date_range', 'get_date_range'),
			new \Twig\TwigFilter('format_1000', 'split_num_by_groups'),
			new \Twig\TwigFilter('numberize', 'numberize'),
			new \Twig\TwigFilter('tel', 'format_tel'),
			new \Twig\TwigFilter('time_range', 'get_time_range'),
			new \Twig\TwigFilter('typograph', 'remove_trailing_prepos')
		];
	}
}

$loader = new \Twig\Loader\FilesystemLoader('../app_html/templates');
$twig = new \Twig\Environment($loader, [
	'autoescape' => false,
	'debug' => $is_dev
]);
if ($is_dev) {
	$twig->addExtension(new \Twig\Extension\DebugExtension());
}
$twig->addExtension(new Twig_Extension());

$htmlmin = new \voku\helper\HtmlMin();
$htmlmin->doOptimizeViaHtmlDomParser(true);
$htmlmin->doRemoveSpacesBetweenTags(true);
$htmlmin->doRemoveOmittedQuotes(false);
$htmlmin->doRemoveOmittedHtmlTags(false);
