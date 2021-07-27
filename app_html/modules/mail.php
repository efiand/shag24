<?php
$transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
$mailer = new Swift_Mailer($transport);
$confirmed_status = 'Подтвержден';

$mailing_success = true; // Будет false только если облом рассылок
// Рассылка пользователю
if ($form['mail_body'] && $do_mail_user) {
	$mail_topic = $form['mail_topic'];
	$mail_body = $htmlmin->minify($twig->createTemplate($form['mail_body'])->render([
		'TEST_RES' => $_POST['test'] ?? '',
		'USER' => $req['name'],
		'PAGE' => $req['page']
	]));

	// Вложение
	if ($form['attach']) {
		$receivers = ''; // уведомления о заявках на подарки не нужны
		$attach_name = $form['attach_name'] ? $form['attach_name'] : $form['attach'];
		$mail_topic = str_replace('{{ ATTACH }}', $attach_name, $mail_topic);
		$mail_body = str_replace('{{ ATTACH }}', $attach_name, $mail_body);
		$file_path = 'media/' . $_['ADMINZONE'] . '/' . $form['attach'];
		$attachment = Swift_Attachment::fromPath($file_path);
		$attachment->setFilename($attach_name . substr($file_path, strrpos($file_path, '.')));
	}

	$message = (new Swift_Message($mail_topic))
		->setFrom($mail_sender)
		->setTo([$req['mail']]);
	if (isset($attachment)) {
		$message->attach($attachment);
	}
	$message->addPart($mail_body, 'text/html');

	if (!$mailer->send($message)) {
		$mailing_success = false;
	}
}

// Рассылка-уведомление по успеху отправки заявки в БД
if (isset($receivers) && $do_mail_receivers) {
	$send = $req;
	if (isset($send['order_id'])) {
		unset($send['order_id'], $send['payment_status']);

		// Если платеж завершен
		// или если заявка платежная, но требуется присылать уведомление даже при незавершенном платеже
		if ($req['payment_status'] === $confirmed_status || $form['notify_uncompl_flag']) {
			$send['order'] = 'Номер заказа: ' . $req['order_id'];
			if ($req['payment_status'] === $confirmed_status) {
				$status_tail = $confirmed_status;
			} else {
				$ticket = get_db_data('SELECT * FROM data_tickets WHERE order_id = ' . $req['order_id'] , 'single');
				$status_tail = $ticket['payment_status'];
			}
			$send['payment_status'] = 'Статус платежа: ' . $status_tail;
		}
	}
	unset($send['id'], $send['form_id']);
	$send['topic'] .= ':';
	if (isset($send['promocode']) && $send['promocode']) {
		$send['promocode'] = 'Промокод: ' . $send['promocode'];
	}
	if (isset($send['instagram']) && $send['instagram']) {
		$send['instagram'] = 'Инстаграм: ' . $send['instagram'];
	}

	$message = (new Swift_Message('From: <' . $_SERVER['HTTP_HOST'] . '>'))
		->setFrom($mail_sender)
		->setTo(explode(', ', $receivers))
		->setBody(strip_tags(implode("\n", $send)));
	if (!$mailer->send($message)) {
		$mailing_success = false;
	}
}
