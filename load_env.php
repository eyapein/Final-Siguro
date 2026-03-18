<?php

function ticketix_load_env($path)
{
	if (!is_file($path) || !is_readable($path)) {
		return;
	}
	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] === '#') {
			continue;
		}
		$parts = explode('=', $line, 2);
		if (count($parts) !== 2) {
			continue;
		}
		$key = trim($parts[0]);
		$value = trim($parts[1]);
		if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
			$quote = $value[0];
			if (substr($value, -1) === $quote) {
				$value = substr($value, 1, -1);
			}
		}
		if (!array_key_exists($key, $_ENV) && !getenv($key)) {
			putenv($key . '=' . $value);
			$_ENV[$key] = $value;
		}
	}
}
