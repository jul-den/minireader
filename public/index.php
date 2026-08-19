<?php

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy(); // или просто session_write_close();
}
ini_set('session.auto_start', 0);
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);

function getlang() {
	$chosen = 'ru'; // язык по умолчанию
	$supported = ['ru', 'en']; //, 'de'];
	$preferred = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
	$preferred = ($preferred)? explode(',', $preferred) : [];

	foreach ($preferred as $lang) {
		// Проверяем точное совпадение или совпадение по первому сегменту (ru-RU → ru)
		$lang = trim($lang);
		// Отсекаем параметр качества (;q=...)
		if (($pos = strpos($lang, ';')) !== false) {
			$lang = substr($lang, 0, $pos);
		}
		// Извлекаем базовый язык (например, из "ru-RU" получаем "ru")
		$base = explode('-', $lang)[0];

		// Проверяем: точное совпадение или совпадение базового
		if (in_array($lang, $supported)) {
			$chosen = $lang;
			break;
		} elseif (in_array($base, $supported)) {
			$chosen = $base;
			break;
		}
	}

	return '../locals/'.$chosen.'.php';
}

if (file_exists($lang = getlang())) {
	require_once $lang;
}
unset($lang);

require_once "../minireader.php";