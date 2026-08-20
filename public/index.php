<?php

if (session_status() === PHP_SESSION_ACTIVE)
    session_destroy(); // session_write_close();

ini_set('session.auto_start', 0);
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);

function getlang() {
	$chosen = 'ru'; // язык по умолчанию
	$supported = ['ru', 'en']; //, 'de'];
	$preferred = isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : false;
	$preferred = ($preferred)? explode(',', $preferred) : [];

	foreach ($preferred as $lang) {
		$lang = trim($lang);
		if (($pos = strpos($lang, ';')) !== false)
			$lang = substr($lang, 0, $pos);
		$base = explode('-', $lang)[0];
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