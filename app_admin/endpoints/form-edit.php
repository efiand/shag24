<?php

$form = $payload['form'];

$query = 'UPDATE data_forms SET';
if ($form['opener_title']) {
	$query .= ' opener_title = "' . prepare_post_data($form['opener_title']) . '",';
} else {
	$query .= ' opener_title = NULL,';
}
if ($form['topic']) {
	$query .= ' topic = "' . prepare_post_data($form['topic']) . '",';
} else {
	$query .= ' topic = NULL,';
}
if ($form['promocode']) {
	$query .= ' promocode = "' . prepare_post_data($form['promocode']) . '",';
} else {
	$query .= ' promocode = NULL,';
}
if ($form['promocode_status']) {
	$query .= ' promocode_status = "' . prepare_post_data($form['promocode_status']) . '",';
} else {
	$query .= ' promocode_status = NULL,';
}
if ($form['res_status']) {
	$query .= ' res_status = "' . prepare_post_data($form['res_status']) . '",';
} else {
	$query .= ' res_status = NULL,';
}
if ($form['message_field']) {
	$query .= ' message_field = "' . prepare_post_data($form['message_field']) . '",';
} else {
	$query .= ' message_field = NULL,';
}
if ($form['choose_field']) {
	$query .= ' choose_field = "' . prepare_post_data($form['choose_field']) . '",';
} else {
	$query .= ' choose_field = NULL,';
}
if ($form['submit_title']) {
	$query .= ' submit_title = "' . prepare_post_data($form['submit_title']) . '",';
} else {
	$query .= ' submit_title = NULL,';
}
if ($form['payment_target']) {
	$query .= ' payment_target = "' . prepare_post_data($form['payment_target']) . '",';
} else {
	$query .= ' payment_target = NULL,';
}
if ($form['payment_doc']) {
	$query .= ' payment_doc = "' . prepare_post_data($form['payment_doc']) . '",';
} else {
	$query .= ' payment_doc = NULL,';
}
if ($form['mail_promo_topic']) {
	$query .= ' mail_promo_topic = "' . prepare_post_data($form['mail_promo_topic']) . '",';
} else {
	$query .= ' mail_promo_topic = NULL,';
}
if ($form['mail_promo_body']) {
	$query .= ' mail_promo_body = "' . prepare_post_data($form['mail_promo_body']) . '",';
} else {
	$query .= ' mail_promo_body = NULL,';
}
if ($form['mail_topic']) {
	$query .= ' mail_topic = "' . prepare_post_data($form['mail_topic']) . '",';
} else {
	$query .= ' mail_topic = NULL,';
}
if ($form['mail_body']) {
	$query .= ' mail_body = "' . prepare_post_data($form['mail_body']) . '",';
} else {
	$query .= ' mail_body = NULL,';
}
if ($form['attach']) {
	$query .= ' attach = "' . prepare_post_data($form['attach']) . '",';
} else {
	$query .= ' attach = NULL,';
}
if ($form['attach_name']) {
	$query .= ' attach_name = "' . prepare_post_data($form['attach_name']) . '",';
} else {
	$query .= ' attach_name = NULL,';
}
if ($form['test']) {
	$query .= ' test = "' . prepare_post_data($form['test']) . '",';
} else {
	$query .= ' test = NULL,';
}
if ($form['description']) {
	$query .= ' description = "' . prepare_post_data($form['description']) . '",';
} else {
	$query .= ' description = NULL,';
}
$query .= ' price = '
	. (int) $form['price'] . ', price_promocode = '
	. (int) $form['price_promocode'] . ', has_city = '
	. (int) $form['has_city'] . ', has_promocode = '
	. (int) $form['has_promocode'] . ', require_message = '
	. (int) $form['require_message'] . ', has_instagram = '
	. (int) $form['has_instagram'] . ', notify_uncompl_flag = '
	. (int) $form['notify_uncompl_flag'] . ', adminzone = "' . $adminzone . '" WHERE id = ' . $form['id'];
set_db_data($query);
