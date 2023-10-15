<?php

$site = $payload['site'];

$query = 'UPDATE data_sites SET';
if ($site['title']) {
	$query .= ' title = "' . prepare_post_data($site['title']) . '",';
}
if ($site['alias']) {
	$query .= ' alias = "' . prepare_post_data($site['alias']) . '",';
}
if ($site['description']) {
	$query .= ' description = "' . prepare_post_data($site['description']) . '",';
}
if ($site['mail']) {
	$query .= ' mail = "' . $site['mail'] . '",';
}
if ($site['tel']) {
	$query .= ' tel = "' . trim($site['tel']) . '",';
}
if ($site['head_code']) {
	$query .= ' head_code = "' . prepare_post_data($site['head_code']) . '",';
}
if ($site['foot_code']) {
	$query .= ' foot_code = "' . prepare_post_data($site['foot_code']) . '",';
}
if ($site['og_image']) {
	$query .= ' og_image = "' . prepare_post_data($site['og_image']) . '",';
}
if ($site['socials']) {
	$query .= ' socials = "' . prepare_post_data($site['socials']) . '",';
}
if ($site['menu']) {
	$query .= ' menu = "' . prepare_post_data($site['menu']) . '",';
}
if ($site['shop']) {
	$query .= ' shop = "' . trim($site['shop']) . '",';
}
if ($site['sender']) {
	$query .= ' sender = "' . $site['sender'] . '",';
}
if ($site['sender_name']) {
	$query .= ' sender_name = "' . prepare_post_data($site['sender_name']) . '",';
}
$query .= ' has_place_chooser = ' . (int) $site['has_place_chooser']
	. ', has_master_chooser = ' . (int) $site['has_master_chooser']
	. ', has_place_events_page = ' . (int) $site['has_place_events_page']
	. ' WHERE id = ' . $site['id'];
set_db_data($query);
