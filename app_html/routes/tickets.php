<?php

// Особая функциональность асинхронной передачи заявок

header('Content-Type: application/json');
$res['status'] = ''; // будет переопределяться в жизненном цикле скрипта

// Своеобразный файрвол
if (!isset($_POST['agree'])) {
	$res['status'] = 'Согласие не подтверждено.';
} else if (!isset($_POST['form_id']) || !isset($_POST['page']) || !isset($_POST['topic'])) {
	$res['status'] = 'Недопустимый источник.';
} else if (!isset($_POST['name'])) {
	$res['status'] = 'Представьтесь, пожалуйста!';
} else if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	$res['status'] = 'Введите корректный e-mail!';
}
if ($res['status']) {
	exit(json_encode($res, JSON_UNESCAPED_UNICODE));
}

// Все проверки пройдены
// статус будет переопределяться в зависимости от условий
$default_error_status = 'Ошибка отправки сообщения. Попробуйте ещё раз.';
$res['status'] = $default_error_status;

$req = $_POST; // Массив данных для записи в БД и в рассылку-уведомление

$form = get_db_data('SELECT * FROM data_forms WHERE id = ' . $req['form_id'], 'single');

$do_pay = !!$form['payment_target'];
$discount_mode = false; // режим скидки при оплате
$do_db = true;
$do_mail_user = !$form['payment_target'] && !!$form['mail_topic']; // письмо пользователю, если есть, только после оплаты

// Отв. лица определяются на уровне страницы и передаются в каждой форме, уведомление только после оплаты (если у формы нет признака явной отправки независимо от статуса платежа)
$do_mail_receivers = $form['notify_uncompl_flag'] || (!$form['payment_target'] && isset($req['receivers']));

// Начинаем обрабатывать входные данные
$req['topic'] = prepare_post_data($req['topic']);
$req['name'] = prepare_post_data($req['name']);

// Начинаем собирать запрос на проверку дублей
$double_query = 'SELECT id FROM data_tickets WHERE
	topic = "' . $req['topic'] . '" AND
	page = "' . $req['page'] . '" AND
	name = "' . $req['name'] . '" AND
	email = "' . $req['email'] . '"';

if (isset($req['phone'])) {
	$req['phone'] = substr(preg_replace('/[^0-9+]/', '', $req['phone']), 0, 14);
	$tel_size = strlen($req['phone']);
	if ($tel_size === 11 && (substr($req['phone'], 0, 1) === '8' || substr($req['phone'], 0, 1) === '7')) {
		$req['phone'] = '+7' . substr($req['phone'], 1);
	} else if ($tel_size === 10) {
		$req['phone'] = '+7' . $req['phone'];
	}
	$double_query .= ' and phone = "' . $req['phone']. '"';
}

if (isset($req['instagram'])) {
	$req['instagram'] = preg_replace('/\/$/', '', trim($req['instagram']));
	$req['instagram'] = str_replace(['-', '?'], ['_', ''], trim($req['instagram']));
	if (filter_var($req['instagram'], FILTER_VALIDATE_URL) && false === strpos($req['instagram'], 'instagram.com')) {
		$req['instagram'] = '';
	}
	$req['instagram'] = preg_replace(['/^.*\//', '/[а-яА-ЯёЁ@]/', '/\?.*$/'], '', $req['instagram']);
	if (!$req['instagram'] || preg_match('/^\_+$/', $req['instagram'])) {
		unset($req['instagram']);
	} else {
		$double_query .= ' and instagram = "' . $req['instagram']. '"';
	}
}

if (!isset($req['message'])) {
	$req['message'] = '';
} else {
	$req['message'] = preg_replace('/\s+/', ' ', trim($req['message']));
}

// Тест собираем в Сообщение
if (isset($req['test'])) {
	if ($req['message']) {
		$req['message'] .= ' ';
	}
	$req['message'] .= 'Результат теста: ' . $req['test'];
	unset($req['test'], $req['test-q']);
}

// Кастомный список вариантов. Под него нет колонки, добавим к сообщению
if (isset($req['choose'])) {
	if ($req['message']) {
		$req['message'] .= ' ';
	}
	$req['message'] .= 'Выбран вариант: ' . $req['choose'];
	unset($req['choose']);
}

if (!$req['message']) {
	unset($req['message']);
} else {
	$req['message'] = prepare_post_data($req['message']);
	$double_query .= ' and message = "' . $req['message']. '"';
}

$promo_mode = false;
if (isset($req['promocode'])) {
	$req['promocode'] = trim($req['promocode']);

	if ($req['promocode'] && $do_pay && $form['promocode']
		&& false !== strpos(str_replace(' ', '', mb_strtolower($req['promocode'])), mb_strtolower($form['promocode']))) {
		if ($form['price_promocode']) {
			$do_pay = true;
			$do_mail_receivers = false;
			$do_mail_user = false;
			$discount_mode = true;
			$form['price'] = $form['price_promocode'];
			$req['promocode'] .= ' (' . $form['price'] . ' р.)';
		} else if ($form['promocode_status']) {
			// Если промокод совпал и задано статусное сообщение, включаем промо-режим: блокируем оплату, показываем статус
			$res['status'] = $form['promocode_status'];
			$promo_mode = true;
			unset($req['order']);
			$req['payment_status'] = 'Совпадение промокода';
			$do_pay = false; // оплату не производим: по совпавшему промокоду ручная обработка для оплаты со скидкой
			if ($form['mail_promo_body']) {
				// если есть тело спец. письма, шлем его
				$do_mail_user = true;
				if ($form['mail_promo_topic']) {
					$form['mail_topic'] = $form['mail_promo_topic'];
				}
				$form['mail_body'] = $form['mail_promo_body'];
			} else {
				// письмо, отправляемое по факту оплаты, в данном случае пользователю не шлем
				$do_mail_user = false;
			}
			$do_mail_receivers = isset($req['receivers']);
		}
	}
}

// Меняем зарезервированное (но нужное банку) название поля order на БД-шное order_id
if (isset($req['order'])) {
	$req['order_id'] = $req['order'];
	unset($req['order']);
}

// Проверка на дублирование заявки
if (get_db_data($double_query, 'single')) {
	$res['status'] = $req['name'] . '! Ваша предыдущая заявка успешно отправлена, мы обязательно свяжемся с Вами!';
	$do_db = !!$form['payment_target'] && !$promo_mode;
	$do_mail_receivers = $form['payment_target'] && $form['notify_uncompl_flag'];
	$do_mail_user = false;
	$_['double'] = true; // Ответ форме, например чтобы не обрабатывать туннель продаж
}

if ($do_db) {
	// Удаляем ненужные в таблице БД заявок поля
	unset($req['agree'], $req['receivers'], $req['sender'], $req['sender_name']);
	$ticket_query = 'INSERT INTO data_tickets (' . implode(', ', array_keys($req)). ') VALUES ("' . implode('", "', $req). '")';
	if (set_db_data($ticket_query)) {
		if (!$promo_mode) {
			$res['status'] = $form['res_status'] ? $form['res_status'] : $req['name'] . '! Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.';
		} else if ($is_dev) {
			$res['log'] = 'Промокод совпал';
		}
	} else {
		$do_pay = false;
		$do_mail_receivers = false;
		$do_mail_user = false;
		$res['status'] = $default_error_status;
		if ($is_dev) {
			$res['query'] = $ticket_query;
			$res['db_log'] = mysqli_error($db) ?? 'No db problems';
		}
	}
}

// Рассылки - если есть необходимость и нет проблем с отправкой заявки в БД
if ($do_mail_receivers || $do_mail_user) {
	if ($do_mail_receivers) {
		$receivers = trim($_POST['receivers']);
	}
	$mail_sender = [$_POST['sender'] => $_POST['sender_name']];

	require_once '../app_html/modules/mail.php';

	// Переданный статус отправки
	if ($mailing_success) {
		if ($do_mail_user && !$promo_mode) {
			if ($form['res_status']) {
				$res['status'] = $form['res_status'];
			} else {
				$res['status'] = $req['name'] . '! Ваша заявка отправлена. На почту <b>' . $req['mail'] . '</b> выслано письмо с дальнейшими инструкциями. <strong class="accent">Если не нашли письмо, проверьте в спаме.</strong>';
			}
		}
	}	else {
		$res['status'] = $default_error_status;
	}
}

if ($do_pay) {
	$res['status'] = $req['name'] . '! Благодарим за регистрацию. Через несколько секунд Вы будете перемещены на страницу оплаты.';
	if ($discount_mode) {
		$res['status'] .= ' Стоимость заказа по промокоду составит ' . split_num_by_groups($form['price_promocode']) . ' руб.';
	}
	if ($form['mail_topic']) {
		$res['status'] .= ' После оплаты дополнительная информация придёт Вам на <b>' . $req['mail'] . '</b>. <strong class="accent">Если не найдёте письмо, проверьте в спаме.</strong>';
	}
}

// параметры заявок, заодно и признак того, что ответ от API заявок
$res['do_pay'] = $do_pay;
$res['price'] = $form['price']; // цена, которая может меняться

$res['status'] = $twig->createTemplate($res['status'])->render([
	'USER' => $req['name']
]);

exit(json_encode($res, JSON_UNESCAPED_UNICODE));
