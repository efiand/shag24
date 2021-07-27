<?php

$files = scandir($media_dir);
$filedates = [];
foreach ($files as $file) {
	if ($payload['imagesOnly'] && !(substr($file, -4) === '.jpg' || substr($file, -4) === '.png')) {
		continue;
	}

	if (!$payload['filter'] || false !== strpos($file, strtolower($payload['filter']))) {
		$filedates[$file] = filectime($media_dir . $file);
	}
}
if ($payload['sortByDate']) {
	arsort($filedates);
} else {
	ksort($filedates);
}
clearstatcache();
$_['files'] = array_keys($filedates);
