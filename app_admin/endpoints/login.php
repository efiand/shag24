<?php

if ($payload) {
	$login = trim($payload['login']);
	$user = get_db_data('SELECT
		id,
		login,
		password,
		shodhan_flag,
		admin_flag
	FROM data_people WHERE login = "' . $login . '" AND password IS NOT NULL', 'single');
	if (isset($user['login'])) {
		// Пользователь найден
		if (password_verify($payload['password'], $user['password'])) {
			$_SESSION['login'] = $login;
			$_['allow'] = (bool) $user['admin_flag'];
			$_['shodhan'] = $adminzone === 'khara' && (bool) $user['shodhan_flag'] && !$_['allow']; // режим инструктора Шодхан
			$_['id'] = $user['id'];

			if ($_['shodhan']) {
				$_['login'] = $login;
			} else if (!$_['allow']) {
				$_['status'] = 'Доступ запрещен!';
			}
		} else {
			$_['status'] = 'Неверный пароль.';
			$_['login'] = $login;
			$_['shodhan'] = false;
			$_['allow'] = false;
		}
	} else {
		$_['status'] = 'Пользователь не найден.';
		$_['login'] = '';
		$_['shodhan'] = false;
		$_['allow'] = false;
	}
}
