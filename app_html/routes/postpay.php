<?php

function GUID() {
	if (function_exists('com_create_guid') === true) {
		return trim(com_create_guid(), '{}');
	}

	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

$confirmed_status = 'Подтвержден';
$res = 'OK'; // Может измениться, если неудача отправки писем
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$log = fopen('../logs/postpay.log', 'a+');
$rid = GUID();

function log_item($str) {
	global $log, $rid;

	fwrite($log, '[' . date('Y-m-d H:i:s', time()) . '][' . $rid . '] ' . $str . "\n");
}

log_item('Источник: ' . $_SERVER['REMOTE_ADDR']);

if (json_last_error() === JSON_ERROR_NONE && isset($payload['Status'])) {
	log_item('Данные от банка: ' . $raw);
	// Сбор сведений (переменные названы совместимо с API заявок)
	$req = get_db_data('SELECT * FROM data_tickets WHERE order_id = ' . $payload['OrderId'], 'single');
	log_item('Получены данные заявки ' . $payload['OrderId'] . ' из нашей БД: ' . json_encode($req));

	if ($req['id']) {
		if ($payload['Status'] === 'CONFIRMED') {
			$req['payment_status'] = $confirmed_status; // Иногда статус в письмо приходит ошибочный
			$form = get_db_data('SELECT * FROM data_forms WHERE id = ' . $req['form_id'], 'single');

			// Могут быть URL с хэшами - тогда удаляем перед запросом получателей
			$hash_pos = strpos($req['page'], '#');
			if ($hash_pos) {
				$base_page = substr($req['page'], 0, strpos($req['page'], '#'));
			} else {
				$base_page = $req['page'];
			}
			log_item('Заявка с подтвержденным статусом относится к странице: ' . $base_page);
			$pagedata = get_db_data('SELECT * FROM view_pages2mailing WHERE page = "' . $base_page . '"', 'single');

			$receivers = $pagedata['receivers']; // отв. лица определяются на уровне страницы
			$do_mail_receivers = !!$receivers;
			$do_mail_user = $form['mail_topic'] && $form['mail_body'];
			if ($do_mail_receivers || $do_mail_user) {
				$mail_sender = [$pagedata['sender'] => $pagedata['sender_name']];
				log_item('Отправка уведомления адресатам: ' . $receivers);
				require_once '../app_html/modules/mail.php';
			}

			// Обновление БД
			if ($mailing_success) {
				log_item('Уведомление успешно отправлено.');
				$db_status = set_db_data('UPDATE data_tickets SET payment_status = "' . $confirmed_status . '" WHERE id = ' . $req['id']);
				log_item('Обновлен статус заявки ' . $req['id']);

				if (!$db_status) {
					$res = '';
					log_item('Обновление заявки ' . $req['id'] . ' не удалось... ' . $db_status);
				} else {
					// Удаляем все возможные дубли (по имени и почте)
					set_db_data('DELETE LOW_PRIORITY FROM data_tickets WHERE topic = "'
						. $req['topic'] . '" AND page = "'
						. $req['page'] . '" AND name = "'
						. $req['name'] . '" AND phone = "'
						. $req['phone'] . '" AND email = "'
						. $req['email'] . '" AND form_id = '
						. $req['form_id'] . ' AND payment_status <> "' . $confirmed_status . '"');
					log_item('Дубли заявки ' . $req['id'] . ' удалены');
				}
			} else {
				log_item('Ошибки рассылок, посылаем банку требование выслать данные повторно!');
				$res = '';
			}
		} else {
			$status = get_db_data('SELECT status FROM data_payment_status WHERE alias = "' . $payload['Status'] . '"', 'single')['status'];
			set_db_data('UPDATE data_tickets SET payment_status = "' . $status . '" WHERE id = ' . $req['id']);
			log_item('Платеж по заявке ' . $req['id'] . ' не подтвержден. Обновление статуса: ' . $status);
		}
	}
	log_item('Ответ банку: ' . $res);
} else {
	log_item('Неверный формат запроса');
}

fclose($log);

// Ответ банку (https://oplata.tinkoff.ru/develop/api/notifications/setup-response/)
if ($res === 'OK') {
	header('HTTP/1.0 200 OK');
}

exit($res);
