<?php

$file = $payload['fileData'];

if ($file['delete']) {
	// удаление
	unlink($media_dir . $file['delete']);
} else if ($file['modified']) {
	// добавление/модификация
	if (isset($file['src']) && $file['src']) {
		// если понадобится передавать прямо в JSON
		$content = base64_decode(preg_replace('/\s+/', '+', preg_replace('/data:image\/.*?;base64\,/', '', $file['src'])));
		file_put_contents($media_dir . $file['name'], $content);
	} else if (isset($_FILES['file'])) {
		$upload_status = move_uploaded_file($_FILES['file']['tmp_name'], $media_dir . $file['name']);
		if ($is_dev) {
			$_['file'] = $_FILES['file'];
			$_['uploadStatus'] = $upload_status;
		}
	}
	if ($file['oldName'] && $file['oldName'] !== $file['name']) {
		unlink($media_dir . $file['oldName']);
	}
} else if ($file['oldName'] && $file['oldName'] !== $file['name']) {
	rename($media_dir . $file['oldName'], $media_dir . $file['name']);
}
