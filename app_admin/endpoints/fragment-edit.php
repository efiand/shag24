<?php

$fragment = $payload['fragment'];
$value = $fragment['value'] ? trim($fragment['value']) : '';
$type = $fragment['type'] ? trim($fragment['type']) : '';
$do_flag = true;

if ($alias = trim($fragment['alias'])) {
	$queryBody = '';

	if ($type === 'copy' && (int) $value) {
		$copy = get_db_data('SELECT * FROM data_fragments WHERE id = ' . (int) $value, 'single');
		if (isset($copy['id'])) {
			$queryBody = '
				sort_order = ' . $copy['sort_order'] . ',
				alias = "' . $copy['alias'] . '",
				value = "' . prepare_post_data($copy['value']) . '",
				description = "' . prepare_post_data($copy['description']) . '",
				type = "' . $copy['type'] . '",
				is_published = ' . $copy['is_published'];
		} else {
			$do_flag = false;
		}
	} else {
		if ($fragment['sort_order']) {
			$queryBody .= ' sort_order = ' . $fragment['sort_order'] . ',';
		}
		if ($fragment['description']) {
			$queryBody .= ' description = "' . prepare_post_data($fragment['description']) . '",';
		}
		if ($type) {
			$queryBody .= ' type = "' . $type . '",';
			if ($type === 'youtube') {
				$value = str_replace('.be/', 'be.com/embed/', $value);
			}
		}
		$queryBody .= ' value = "' . prepare_post_data($value) . '",';
		$queryBody .= ' is_published = ' . (int) $fragment['is_published'] . ',';
		$queryBody .= ' alias = "' . $alias . '"';
	}

	if ($do_flag) {
		if ((int) $fragment['id'] > 0) {
			$query = 'UPDATE data_fragments SET' . $queryBody . ' WHERE id = ' . $fragment['id'];
		} else {
			$query = 'INSERT INTO data_fragments SET page_id = ' . $payload['pageId'] . ',' . $queryBody;
		}

		set_db_data($query);
	}
}
