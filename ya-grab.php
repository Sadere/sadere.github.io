<?php

$target_folder = "/home/sample/work/yandex-page";

$doc = gwt("https://yandex.ru");

preg_match_all('#<(link|img|script) .*(href|src)="(.*)"[^>]*>#U', $doc, $match);
preg_match_all('#url\("?((https:)?//.*)"?\)#U', $doc, $match_style);

$links = array_merge($match[3], $match_style[1]);

// Заменяем ссылки на ассеты

foreach ($links as $link) {
	$original_link = $link;

	if(substr($link, 0, 2) == "//") {
		$link = "https://" . substr($link, 2);
	}
	$parse = parse_url($link);

	if(!isset($parse["path"]))
		continue;

	$parts = explode("/", $parse["path"]);

	$filename = array_pop($parts);
	$path = $target_folder . implode("/", $parts);

	if(!file_exists($path))
		mkdir($path, 0774, true);

	if(!file_exists($target_folder . $parse["path"]))
		copy($link, $target_folder . $parse["path"]);

	$doc = str_replace($original_link, substr($parse["path"], 1), $doc);
}

$doc = str_replace('<div class="home-logo__default" role="img" aria-label="Яндекс"></div>', '<a href="http://mail.ru"><div class="home-logo__default" role="img" aria-label="Яндекс"></div></a>', $doc);

// Сохраняем саму страницу

file_put_contents("$target_folder/index.html", $doc);



function gwt($url, $curlopts = array()) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
	curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36");
	curl_setopt_array($ch, $curlopts);
	$doc = curl_exec($ch);
	if(!$doc) {
		$eid = curl_errno($ch);
		file_put_contents("curl_errors", "url: $url, eid: $eid\r\n");
	}
	curl_close($ch);
	return $doc;
}

?>