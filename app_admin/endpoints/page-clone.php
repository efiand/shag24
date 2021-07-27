<?php

$clone_id = $payload['clonePageId'] ?? 0;
$save_params = !!$payload['changeCloneParams'];

if ($clone_id) {
	if ($save_params) {
		$page = get_db_data('SELECT * FROM data_pages WHERE id = ' . $clone_id, 'single');
		set_db_data('UPDATE data_pages SET
			title = "' . prepare_post_data($page['title']) . '",
			description = "' . prepare_post_data($page['description']) . '",
			receivers = "' . $page['receivers'] . '",
			search_flag = ' . $page['search_flag'] . '
		WHERE id = ' . $payload['pageId']);
	}

	$fragments = get_db_data('SELECT * FROM data_fragments WHERE page_id = ' . $clone_id);
	foreach ($fragments as $fragment) {
		set_db_data('INSERT INTO data_fragments (sort_order, alias, page_id, value, description, type, is_published) VALUES (
			' . $fragment['sort_order'] . ',
			"' . $fragment['alias'] . '",
			' . $payload['pageId'] . ',
			"' . prepare_post_data($fragment['value']) . '",
			"' . prepare_post_data($fragment['description']) . '",
			"' . $fragment['type'] . '",
			' . $fragment['is_published'] . '
		)');
	}
}
