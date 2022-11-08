<?php

/* Параметры */
set_time_limit(-1);
ini_set('max_execution_time', 0);
date_default_timezone_set("Europe/Moscow");
mb_internal_encoding("UTF-8");
setlocale(LC_CTYPE, 'UTF-8');
error_reporting(E_ERROR | E_PARSE);
session_start();

/* Плагины */
include_once('plugins/parsedown.php');

/* Данные */
$config = json_decode(file_get_contents("/var/www/html/tdkp/bin/config.json"), true);

/* Функции */
function discordApiRequest($url, $post = null, $headers = array())
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	$response = curl_exec($ch);

	if ($post) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

	$headers[] = 'Accept: application/json';

	if ($_SESSION['access_token']) $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($ch);
	return json_decode($response);
}

function sacredConnect($endpoint, $query = "")
{
	$ch = curl_init('localhost:3000/' . $endpoint . ($query ? '?' . $query : ''));
	curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => TRUE));
	$response = curl_exec($ch);
	return $response;
}

function discordApiLogout($url, $data = array())
{
	$ch = curl_init($url);
	curl_setopt_array($ch, array(
		CURLOPT_POST => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
		CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
		CURLOPT_POSTFIELDS => http_build_query($data),
	));
	$response = curl_exec($ch);
	return json_decode($response);
}

function getRoles($table)
{
	global $connection;

	$data = $connection->query("SELECT `id`, `name`, `role_code` FROM `$table` ORDER BY `id` DESC")->fetchAll(PDO::FETCH_ASSOC);

	foreach ($data as $entry) {
		$roles[$entry['role_code']] = $entry;
	}

	return $roles;
}

function getLoginData($dskid)
{
	global $connection;

	$data = $connection->prepare("SELECT `party`, `clan` FROM `users` WHERE `dsk_id` = ?");
	$data->execute(array($dskid));
	$data = $data->fetch(PDO::FETCH_ASSOC);

	return array($data['party'], $data['clan']);
}

function getUser($id)
{
	global $connection;

	$query = "SELECT `users`.*, 
	`clans`.`name` AS kname, `clans`.`id` AS cid, `parties`.`name` AS pname, `classes`.`name` AS cname, 
	`servers`.`code` AS `scode`, `servers`.`id` as `sid`, `servers`.`name` AS `sname`
		FROM `users` 
		LEFT JOIN `clans` ON `clans`.`id` = `users`.`clan` 
		LEFT JOIN `parties` ON `parties`.`id` = `users`.`party` 
		LEFT JOIN `classes` ON `classes`.`id` = `users`.`class` 
		LEFT JOIN `servers` ON `servers`.`id` = `users`.`server` 
		WHERE `users`.`id` = ?";

	$user = $connection->prepare($query);
	$user->execute(array($id));
	$user = $user->fetch(PDO::FETCH_ASSOC);

	$skills = $connection->prepare("SELECT `skills`.`item`, `items`.`name` FROM `skills` LEFT JOIN `items` ON `skills`.`item` = `items`.`id` WHERE `user` = ?");
	$skills->execute(array($id));
	$user['skills'] = $skills->fetchAll(PDO::FETCH_ASSOC);

	$wishlist = $connection->prepare("SELECT `wishlist`.`item`, `items`.`name` FROM `wishlist` LEFT JOIN `items` ON `wishlist`.`item` = `items`.`id` WHERE `user` = ?");
	$wishlist->execute(array($id));
	$user['wishlist'] = $wishlist->fetchAll(PDO::FETCH_ASSOC);

	$items = $connection->prepare("SELECT `holdings`.`item`, `items`.`name` FROM `holdings` LEFT JOIN `items` ON `holdings`.`item` = `items`.`id` WHERE `user` = ?");
	$items->execute(array($id));
	$user['items'] = $items->fetchAll(PDO::FETCH_ASSOC);

	$timezone = $connection->prepare("SELECT * FROM `timezone` WHERE `id` = ?");
	$timezone->execute(array($user['timezone']));
	$user['timezone'] = $timezone->fetch(PDO::FETCH_ASSOC);

	return $user;
}

function cdate($format, $date = null)
{
	$dt = new DateTime();
	$dt->setTimezone(new DateTimeZone($_SESSION['user']['timezone']['value']));
	if ($date) $dt->setTimestamp($date);
	return $dt->format($format);
	//return date($format, $date);
}

function getCPData($id)
{
	global $connection;

	$query = "SELECT `parties`.`id` AS party, `parties`.`name` AS pname, `parties`.`points`, `parties`.`dkp_rating` AS pdkp_rating, `parties`.`points_cards` AS cpoints, 
	`parties`.`points_collections` AS ppoints_collections, `parties`.`rating_collections` AS prating_collections, `parties`.`rating_cards` AS prating_cards, 
	`parties`.`total_items` AS ptotal_items, `parties`.`total_bosses` AS ptotal_bosses,
	`servers`.`code` AS `scode`, `servers`.`id` as `sid`, `servers`.`name` AS `sname`
		FROM `parties` 
		LEFT JOIN `servers` ON `servers`.`id` = `parties`.`server` 
		WHERE `parties`.`id` = ?";

	$user = $connection->prepare($query);
	$user->execute(array($id));
	$user = $user->fetch(PDO::FETCH_ASSOC);

	return $user;
}

function dsSendMessageBoss($server, $text, $code, $icon = 'https://justdkp.com/img/em1.png')
{
	global $connection;

	$data = $connection->prepare("SELECT `role_code` FROM `channels` WHERE `server` = ? AND `type` = ? LIMIT 1;");
	$data->execute(array($server, 'boss'));
	$message['channel'] = $data->fetchColumn();

	$message['embeds'] = json_encode([
		"type" => "rich",
		"title" => "Новое событие",
		"description" => $text,
		"color" => "0xeb4f06",
		"image" => [
			"url" => "https://justdkp.com/img/blnk.png"
		],
		"thumbnail" => [
			"url" => $icon
		]
	]);

	$message['components'] = json_encode([
		"type" => 1,
		"components" => [
			[
				"style" => 3,
				"label" => "Зафиксировать участие",
				"custom_id" => "c-{$code}",
				"disabled" => false,
				"type" => 2
			],
			[
				"style" => 4,
				"label" => "Отменить участие",
				"custom_id" => "c-{$code}-del",
				"disabled" => false,
				"type" => 2
			]
		]
	]);

	sacredConnect('message', http_build_query($message));
}

function dsSendMessageLoot($server, $text, $code, $icon = 'https://justdkp.com/img/em2.png')
{
	global $connection;

	$data = $connection->prepare("SELECT `role_code` FROM `channels` WHERE `server` = ? AND `type` = ? LIMIT 1;");
	$data->execute(array($server, 'drop'));
	$message['channel'] = $data->fetchColumn();

	$message['embeds'] = json_encode([
		"type" => "rich",
		"title" => "Предмет доступен для распределения",
		"description" => $text,
		"color" => "0x0bdb8b",
		"image" => [
			"url" => "https://justdkp.com/img/blnk.png"
		],
		"thumbnail" => [
			"url" => $icon
		]
	]);

	$message['components'] = json_encode([
		"type" => 1,
		"components" => [
			[
				"style" => 3,
				"label" => "Претендовать на лут",
				"custom_id" => "l-{$code}",
				"disabled" => false,
				"type" => 2
			],
			[
				"style" => 4,
				"label" => "Отказаться от лута",
				"custom_id" => "l-{$code}-del",
				"disabled" => false,
				"type" => 2
			]
		]
	]);

	sacredConnect('message', http_build_query($message));
}

function dsSendMessageItems($server, $text, $tag, $icon = 'https://justdkp.com/img/em3.png')
{
	global $connection;

	$data = $connection->prepare("SELECT `role_code` FROM `channels` WHERE `server` = ? AND `type` = ? LIMIT 1;");
	$data->execute(array($server, 'loot'));
	$message['channel'] = $data->fetchColumn();

	$message['content'] = $tag;

	$message['embeds'] = json_encode([
		"type" => "rich",
		"title" => "Предмет успешно распределен",
		"description" => $text,
		"color" => "0x0bbfd",
		"image" => [
			"url" => "https://justdkp.com/img/blnk.png"
		],
		"thumbnail" => [
			"url" => $icon
		]
	]);

	$message['components'] = null;

	sacredConnect('message', http_build_query($message));
}

function loginError($message)
{
	$_SESSION['error'] = $message;

	unset($_SESSION['access_token']);
	unset($_SESSION['user']);

	unset($_COOKIE['access_token']);
	unset($_COOKIE['user']);

	setcookie('access_token', '', time() - 3600, '/');
	setcookie('user', '', time() - 3600, '/');

	header("Refresh:0; url=/?p=gateway");
	die();
}

function timeElapsed($datetime, $full = false)
{
	$now = new DateTime;
	$ago = new DateTime($datetime);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second');
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function lootUpdate()
{
	global $connection;

	/* Автовыдача вещей */
	$data = $connection->prepare("SELECT `loot`.`id`, `loot`.`clan`, `loot`.`server`, `loot`.`discount`, `items`.`name`, `items`.`icon`, `items`.`rarity`, ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) AS passed FROM `loot` JOIN `items` ON `loot`.`item` = `items`.`id` WHERE `loot`.`status` = 0 AND `loot`.`salary` = 0 HAVING (passed BETWEEN 720 AND 2160) AND `items`.`rarity` = 'Редкий';");
	$data->execute();
	$data = $data->fetchAll(PDO::FETCH_ASSOC);

	foreach ($data as $loot) {
		$winner = $connection->prepare("SELECT `applicants`.`user` FROM `applicants` LEFT JOIN `users` ON `users`.`id` = `applicants`.`user` WHERE `loot` = ? ORDER BY `dkp_rating` ASC LIMIT 1");
		$winner->execute(array($loot['id']));
		$winner = $winner->fetch(PDO::FETCH_ASSOC);

		if ($winner) {
			$query = "SELECT `users`.`server` AS `sid`, `users`.`dkp_rating`, `users`.`id` AS `uid`, `users`.`dsk_id`, `users`.`name` AS `to_name`, `items`.`name` AS `item`, `items`.`icon` AS `icon`, `items`.`rarity` AS `rarity`, `items`.`uname` AS `from_name`, `items`.`id` AS `iid`, CEIL(`users`.`dkp`*(`items`.`dkp`/100)) AS price 
				FROM `users`
				JOIN (
					SELECT `items`.`id`, `items`.`dkp`, `items`.`name`, `items`.`rarity`, `items`.`icon`, `clans`.`name` AS uname 
					FROM `loot` 
					JOIN `clans` ON `loot`.`clan` = `clans`.`id` 
					JOIN `items` ON `loot`.`item` = `items`.`id` 
					WHERE `loot`.`id` = ?
				) `items` 
				WHERE `users`.`id` = ?;";

			$item = $connection->prepare($query);
			$item->execute(array($loot['id'], $winner['user']));
			$item = $item->fetch(PDO::FETCH_ASSOC);

			$query = "SELECT GROUP_CONCAT(CONCAT('<@', `adsk_id`, '>') SEPARATOR ' ') FROM `drivers` WHERE `dsk_id` = ?;";

			$drivers = $connection->prepare($query);
			$drivers->execute(array($item['dsk_id']));
			$drivers = $drivers->fetchColumn();

			if ($loot['discount'])
				$item['price'] = ceil($item['price'] / 2);

			$comment = "Вычтено <strong>{$item['price']} pts.</strong> за выкуп <strong>«{$item['item']}»</strong>, <strong>«{$item['to_name']}»</strong> должен будет забрать предмет у КЛа клана <strong>«{$item['from_name']}»</strong>. Место в рейтинге на момент выдачи: <strong>«{$item['dkp_rating']}»</strong>!";

			$dsMessage = "Предмет **«" . $item['item'] . "»** был выдан **«{$item['to_name']}» (#{$item['dkp_rating']})** за {$item['price']} pts. Его можно забрать у КЛа клана **«{$item['from_name']}»**";
			$dsTag = "<@{$item['dsk_id']}> " . $drivers;

			dsSendMessageItems($item['sid'], $dsMessage, $dsTag, "https://justdkp.com/img/icons/loot/{$item['icon']}.png");

			$connection->prepare("UPDATE `loot` JOIN `users` ON `users`.`id` = ? SET `loot`.`points` = ?, `loot`.`rating` = `users`.`dkp_rating`, `loot`.`user` = `users`.`id`, `loot`.`admin` = ?, `loot`.`status` = 1, `time` = NOW() WHERE `loot`.`id` = ?;")->execute(array($item['uid'], $item['price'], $item['uid'], $loot['id']));
			$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array('Transaction', $item['uid'], intval(-1 * $item['price']), $item['uid'], $comment));
			$connection->prepare("DELETE FROM `wishlist` WHERE `user` = ? AND `item` = ?;")->execute(array($item['uid'], $item['iid']));

			userDKPUpdate($item['sid']);
		} else {
			if (!$loot['discount']) {
				$dis = $connection->prepare("UPDATE `loot` SET `discount` = 1, `time` = NOW() WHERE `id` = ?");
				$dis->execute(array($loot['id']));

				$encryption = md5($loot['id']);

				$clan = $connection->prepare("SELECT `name` FROM `clans` WHERE `id` = ?");
				$clan->execute(array($loot['clan']));
				$clan = $clan->fetchColumn();

				$dsMessage = "Предмет **«" . $loot['name'] . "»** доступен для распределения во втором круге по сниженной цене, любой желающий может претендовать на него. Клан: **«" . $clan . "»**";
				dsSendMessageLoot($loot['server'], $dsMessage, $encryption, "https://justdkp.com/img/icons/loot/{$loot['icon']}.png");
			} else {
				$connection->prepare("UPDATE `loot` SET `salary` = 1 WHERE `id` = ?")->execute(array($loot['id']));
			}
		}
	}
}

function PSUpdate($id)
{
	global $connection;

	$user = $connection->prepare("SELECT * FROM `users` WHERE `id` = ?");
	$user->execute(array($id));
	$user = $user->fetch(PDO::FETCH_ASSOC);

	$ps = 0;
	if ($user['reduction'] == 0 || $user['defence'] == 0 || $user['resistance'] == 0 || $user['level'] == 0 || $user['seal'] == 0 || $user['points_cards'] == 0 || $user['collections'] == 0) {
		$ps = 1000;
	} else {
		$ps += $user['reduction'] * 60 + $user['defence'] * 8 + $user['resistance'] * 10;
		$ps += (($user['level'] - 60) * 200) + pow(1.7, $user['level'] - 60);
		$ps += ((ceil($user['seal'] / 6) * 250) + pow(2, $user['seal'] / 5));
		$ps += $user['points_cards'] * 6;
		$ps += $user['collections'] * 4;
		$ps = $ps / 2.35;
	}

	if ($ps < 1000 || is_infinite($ps) || is_nan($ps)) $ps = 1000;

	$psr = $connection->prepare("UPDATE `users` SET `ps` = ? WHERE `id` = ?");
	$psr->execute(array(ceil($ps), $user['id']));
}

function userCardUpdate($serverID)
{
	global $connection;

	/* Рейтинг карт по человеку */
	$connection->query("
		UPDATE `users` a
		INNER JOIN (
		SELECT (@row_number := @row_number + 1) AS rating_cards, users.*
		FROM
		(
			SELECT `users`.`id`, 
            IFNULL(SUM((`heroes_all`-`heroes_hr`-`heroes_lg`) + (`agations_all`-`agations_hr`-`agations_lg`) + (`heroes_hr`+`agations_hr`)*5 + (`heroes_lg`+`agations_lg`)*20), 0) AS `points_cards` FROM `users` 
            WHERE `users`.`server` = $serverID
			GROUP BY `users`.`id` ORDER BY `points_cards` DESC 
		) users
		CROSS JOIN (SELECT @row_number := 0) AS ranking
		) b ON a.`id` = b.`id`
		SET a.`points_cards` = b.`points_cards`, a.`rating_cards` = b.`rating_cards`
	");
}

function userColUpdate($serverID)
{
	global $connection;

	/* Рейтинг коллекций по человеку */
	$connection->query("
		UPDATE `users` a
		INNER JOIN (
		SELECT (@row_number := @row_number + 1) AS rating_collections, users.*
		FROM
		(
			SELECT `users`.`id`, 
            IFNULL(`collections`, 0) AS `collections` FROM `users` 
			WHERE `users`.`server` = $serverID
            GROUP BY `users`.`id` ORDER BY `collections` DESC 
		) users
		CROSS JOIN (SELECT @row_number := 0) AS ranking
		) b ON a.`id` = b.`id`
		SET a.`rating_collections` = b.`rating_collections`
	");
}

function userPSUpdate($serverID)
{
	global $connection;

	/* Рейтинг коллекций по человеку */
	$connection->query("
		UPDATE `users` a
		INNER JOIN (
		SELECT (@row_number := @row_number + 1) AS rating_ps, users.*
		FROM
		(
			SELECT `users`.`id`, 
            IFNULL(`ps`, 0) AS `ps` FROM `users` 
			WHERE `users`.`server` = $serverID
            GROUP BY `users`.`id` ORDER BY `ps` DESC 
		) users
		CROSS JOIN (SELECT @row_number := 0) AS ranking
		) b ON a.`id` = b.`id`
		SET a.`rating_ps` = b.`rating_ps`
	");

	foreach ($connection->query("SELECT `id` FROM `classes`")->fetchAll(PDO::FETCH_ASSOC) as $class) {
		$connection->query("
			UPDATE `users` a
			INNER JOIN (
			SELECT (@row_number := @row_number + 1) AS rating_ps, users.*
			FROM
			(
				SELECT `users`.`id`, 
				IFNULL(`ps`, 0) AS `ps` FROM `users` 
				WHERE `users`.`server` = $serverID 
				AND `users`.`class` = {$class['id']}
				GROUP BY `users`.`id` ORDER BY `ps` DESC 
			) users
			CROSS JOIN (SELECT @row_number := 0) AS ranking
			) b ON a.`id` = b.`id`
			SET a.`rating_ps_class` = b.`rating_ps`
		");
	}
}

function userBonusUpdate($serverID)
{
	global $connection;

	/* Рейтинг бонусов по человеку */
	$connection->query("
		UPDATE `users` a
		INNER JOIN (
			SELECT (@row_number := @row_number + 1) AS rating_bonus, users.*
			FROM
			(
				SELECT `users`.`id`, IF(COUNT(*) > 1, COUNT(*), 0)  as `points_bonus` FROM `users` LEFT JOIN `points` ON `points`.`admin` = `users`.`id` AND `type` = 'Bonus'
				WHERE `users`.`server` = $serverID
				GROUP BY `users`.`id` ORDER BY `points_bonus` DESC
			) users
			CROSS JOIN (SELECT @row_number := 0) AS ranking
		) b ON a.`id` = b.`id`
		SET a.`rating_bonus` = b.`rating_bonus`, a.`points_bonus` = b.`points_bonus`
	");
}

function userBossUpdate($serverID)
{
	global $connection;

	/* Рейтинг бонусов по человеку */
	$connection->query("
		UPDATE `users` a
		INNER JOIN (
			SELECT (@row_number := @row_number + 1) AS rating_boss, users.*
			FROM
			(
				SELECT `users`.`id`, `users`.`total_bosses_epic_last` FROM `users`
				WHERE `users`.`server` = $serverID
				GROUP BY `users`.`id` ORDER BY `total_bosses_epic_last` DESC
			) users
			CROSS JOIN (SELECT @row_number := 0) AS ranking
		) b ON a.`id` = b.`id`
		SET a.`rating_boss` = b.`rating_boss`
	");
}

function userDKPUpdate($serverID)
{
	global $connection;

	/* Рейтинг ДКП по пачке */
	$connection->query("
		UPDATE `users` a
		INNER JOIN (
		SELECT (@row_number := @row_number + 1) AS dkp_rating, users.*
		FROM
		(
			SELECT `users`.`id`, IFNULL(SUM(`points`.`dkp`), 0) AS `dkp` FROM `users` LEFT JOIN `points` ON `users`.`id` = `points`.`user` 
			WHERE `users`.`server` = $serverID
			GROUP BY `users`.`id` ORDER BY `dkp` DESC 
		) users
		CROSS JOIN (SELECT @row_number := 0) AS ranking
		) b ON a.`id` = b.`id`
		SET a.`dkp` = b.`dkp`, a.`dkp_rating` = b.`dkp_rating`
	");
}

/* База данных */
try {
	$connection = new PDO($config['db'], $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+3:00'"]);
} catch (PDOException $e) {
	print "SQL Error: " . $e->getMessage() . "<br>";
	die();
}

/* Фиксирование посещаемости и претендентов на лут */
if ($_GET['c'] || $_GET['l']) {
	if ($_GET['c']) {
		setcookie('last_event', $_GET['c']);
		setcookie('last_event_type', 'boss');
	}

	if ($_GET['l']) {
		setcookie('last_event', $_GET['l']);
		setcookie('last_event_type', 'loot');
	}

	if ($_GET['a']) {
		setcookie('last_action', $_GET['a']);
	}

	if (!$_SESSION['user']) {
		header('Location: /?p=gateway&a=login');
		die();
	}
}

if ($_COOKIE['last_event'] && $_SESSION['user']) {
	if ($_COOKIE['last_event_type'] == 'boss') {
		$event = $connection->prepare("SELECT `events`.`id`, `bosses`.`id` AS bid, `bosses`.`name` FROM `events` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` WHERE md5(`events`.`id`) = ? AND `status` = 0 LIMIT 1");
		$event->execute(array($_COOKIE['last_event']));
		$event = $event->fetch(PDO::FETCH_ASSOC);

		if (empty($event)) {
			$_SESSION['alert'] = "Это событие уже закрыто! События могут закрыть не быстрее чем за пятнадцать минут с момента запуска.";

			unset($_COOKIE['last_event']);
			setcookie('last_event', '', time() - 3600, '/');
		} else {
			if ($_COOKIE['last_action'] == 'remove') {
				$connection->prepare("DELETE FROM `attendance` WHERE `event` = ? AND `user` = ?")->execute(array($event['id'], $_SESSION['user']['id']));

				$_SESSION['message'] = "Спасибо! Посещение «" . $event['name'] . "» успешно удалено.";

				unset($_COOKIE['last_event']);
				unset($_COOKIE['last_action']);
				setcookie('last_event', '', time() - 3600, '/');
				setcookie('last_action', '', time() - 3600, '/');
			} else {
				$clan = $connection->prepare("SELECT `users`.`clan` FROM `users` WHERE `id` = ?");
				$clan->execute(array($_SESSION['user']['id']));
				$clan = $clan->fetchColumn();

				$connection->prepare("REPLACE INTO `attendance`(`event`, `user`, `server`, `boss`, `clan`) VALUES (?,?,?,?,?)")->execute(array($event['id'], $_SESSION['user']['id'], $_SESSION['user']['sid'], $event['bid'], $clan));

				$_SESSION['message'] = "Посещение «" . $event['name'] . "» успешно записано. Нажмите <a href='/?c={$_COOKIE['last_event']}&a=remove'>сюда</a> если перешли по ссылке случайно или не нанесли урон по боссу.";

				unset($_COOKIE['last_event']);
				unset($_COOKIE['last_action']);
				setcookie('last_event', '', time() - 3600, '/');
				setcookie('last_action', '', time() - 3600, '/');
			}
		}
	}

	if ($_COOKIE['last_event_type'] == 'loot') {
		$loot = $connection->prepare("SELECT `loot`.`id`, `items`.`name` FROM `loot` JOIN `items` ON `items`.`id` = `loot`.`item` WHERE md5(`loot`.`id`) = ? AND `status` = 0 LIMIT 1");
		$loot->execute(array($_COOKIE['last_event']));
		$loot = $loot->fetch(PDO::FETCH_ASSOC);

		if (empty($loot)) {
			$_SESSION['alert'] = "Этот предмет уже нашел своего счастливого владельца.";

			unset($_COOKIE['last_event']);
			setcookie('last_event', '', time() - 3600, '/');
		} else {
			if ($_COOKIE['last_action'] == 'remove') {
				$connection->prepare("DELETE FROM `applicants` WHERE `loot` = ? AND `user` = ?")->execute(array($loot['id'], $_SESSION['user']['id']));

				$_SESSION['message'] = "Вы больше не претендуете на «" . $loot['name'] . "».";

				unset($_COOKIE['last_event']);
				unset($_COOKIE['last_action']);
				setcookie('last_event', '', time() - 3600, '/');
				setcookie('last_action', '', time() - 3600, '/');
			} else {
				$limit = $connection->prepare("SELECT 
					IF(`users`.`dkp` >= 1000, 1, 0) AS pointlimit, 
					IF((SELECT COUNT(*) FROM `applicants` JOIN `loot` ON `loot`.`id` = `applicants`.`loot` WHERE (ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) BETWEEN 0 AND 720) AND `applicants`.`user` = `users`.`id` AND `status` <> 1) < 3, 1, 0) AS applylimit
					FROM `users`
					WHERE `users`.id = ?");
				$limit->execute(array($_SESSION['user']['id']));
				$limit = $limit->fetch(PDO::FETCH_ASSOC);

				if ($limit['pointlimit'] == 0) {
					$_SESSION['alert'] = "Для того чтобы претендовать на эту вещь у вас должно быть больше 1000 pts.";
				} else if ($limit['applylimit'] == 0) {
					$_SESSION['alert'] = "Вы уже претендуете на 3 вещи, пожалуйста подождите окончания торгов или откажитесь от одной из них.";
				} else {
					$clan = $connection->prepare("SELECT `users`.`clan` FROM `users` WHERE `id` = ?");
					$clan->execute(array($_SESSION['user']['id']));
					$clan = $clan->fetchColumn();

					$connection->prepare("REPLACE INTO `applicants`(`loot`, `user`, `server`, `clan`) VALUES (?,?,?,?)")->execute(array($loot['id'], $_SESSION['user']['id'], $_SESSION['user']['sid'], $clan));
					$_SESSION['message'] = "Вы стали одним из претендентов на «" . $loot['name'] . "». Нажмите <a href='/?l={$_COOKIE['last_event']}&a=remove'>сюда</a> если перешли по ссылке случайно.";
				}

				unset($_COOKIE['last_event']);
				unset($_COOKIE['last_action']);
				setcookie('last_event', '', time() - 3600, '/');
				setcookie('last_action', '', time() - 3600, '/');
			}
		}
	}

	unset($_COOKIE['last_event_type']);
	setcookie('last_event_type', '', time() - 3600, '/');

	if ($_GET['p'] != 'profile') {
		unset($_SESSION['alert']);
		unset($_SESSION['message']);
	}
}

/* Темплейты */
$templates['events'] = <<<TEMPLATE
<div class="d-flex align-items-start">
	<img src="{{link}}" alt="AV" width="36" height="36" class="rounded-circle me-2">
	<div class="flex-grow-1">
		<small class="float-end text-navy">{{ago}}</small>
		{{comment}}<br />
		<small class="text-muted">{{time}} | Инициатор: {{user}}</small><br />
	</div>
</div>
<hr />
TEMPLATE;

$templates['message'] = <<<TEMPLATE
<div class="alert alert-success alert-dismissible" role="alert" style="margin: 18px 22px 0px 22px;">
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	<div class="alert-message">
		<strong>{{info}}</strong>
	</div>
</div>
TEMPLATE;

$templates['alert'] = <<<TEMPLATE
<div class="alert alert-danger alert-dismissible" role="alert" style="margin: 18px 22px 0px 22px;">
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	<div class="alert-message">
		<strong>{{info}}</strong>
	</div>
</div>
TEMPLATE;

$templates['logerror'] = <<<TEMPLATE
<div class="alert alert-danger alert-dismissible" role="alert">
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	<div class="alert-message">
		<strong>{{info}}</strong>
	</div>
</div>
TEMPLATE;

if ($connection) return $connection;
