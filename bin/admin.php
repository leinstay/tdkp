<?php
include_once("config.php");

/* Бекенд логика для пересчета ДКП очков */
if ($_GET['action'] == "dkpupdate") {
	set_time_limit(20);

	$query = "SELECT `id` FROM `events` WHERE `time` < DATE_SUB(NOW(), INTERVAL 120 MINUTE) AND `status` = 0";

	$events = $connection->prepare($query);
	$events->execute();
	$events = $events->fetchAll(PDO::FETCH_ASSOC);

	foreach ($events as $event) {
		$query = "SELECT `users`.`rating_boss`, `users`.`rating_ps`, `events`.`server`, `events`.`pvp`, `bosses`.`name`, `attendance`.`event`, `attendance`.`user`, `bosses`.`dkp`, `events`.`awakened`, (SELECT COUNT(*) FROM `attendance` WHERE `attendance`.`event` = `events`.`id`) as cnt FROM `attendance` 
		JOIN `events` ON `attendance`.`event` = `events`.`id`
		JOIN `users` ON `attendance`.`user` = `users`.`id`
		JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE `events`.`id` = ?
		GROUP BY `attendance`.`user`";

		$users = $connection->prepare($query);
		$users->execute(array($event['id']));
		$users = $users->fetchAll(PDO::FETCH_ASSOC);

		foreach ($users as $user) {
			$multiplier = 1;

			if (date("G") >= 3 && date("G") <= 9)
				$multiplier += 1;

			if ($user['pvp'])
				$multiplier += 1;

			if ($user['rating_ps'] <= 50)
				$multiplier += ((50 - ($user['rating_ps'] - 1)) * 0.5) / 100;

			if ($user['rating_boss'] <= 50)
				$multiplier += ((50 - ($user['rating_boss'] - 1)) * 0.5) / 100;

			if (!empty($user['awakened']))
				$multiplier += 5;

			$dkp = ceil($multiplier * $user['dkp']);
			$dkpm = $multiplier * 100;

			$user['name'] = empty($user['awakened'])?$user['name']:"Пробужденный ".$user['name'];

			$comment = "Начислено <strong>{$dkp} pts.</strong> за убийство <strong>«{$user['name']}»</strong>";
			if (date("G") >= 3 && date("G") <= 9) $comment .= " во время ночного прайм-тайма";
			if ($user['pvp']) $comment .= " в условиях PVP";
			$comment .= "! [{$dkpm}%]";

			$connection->prepare("REPLACE INTO `points`(`user`, `event`, `admin`, `dkp`, `multiplier`, `comment`) VALUES (?,?,?,?,?,?)")->execute(array($user['user'], $user['event'], 1, $dkp, $multiplier, $comment));
		}

		if (isset($users[0])) {
			userDKPUpdate($users[0]['server']);
			userBossUpdate($users[0]['server']);
		}

		$connection->prepare("UPDATE `events` SET `status` = 1 WHERE `id` = ?;")->execute(array($event['id']));
	}

	die();
}

/* Бекенд логика для пересчета респаунов */
if ($_GET['action'] == "timeshift") {
	set_time_limit(20);

	$query = "SELECT `code` FROM `servers`";

	$server = $connection->prepare($query);
	$server->execute();
	$server = $server->fetchAll(PDO::FETCH_ASSOC);

	foreach ($server as $srv) {
		$servercode = $srv['code'];

		$connection->query("UPDATE `bosses` SET `next_spawn_{$servercode}` = null WHERE `id` NOT IN (56,57,58,36,60,62)");

		$data = $connection->prepare("SELECT * FROM `bosses`;");
		$data->execute();
		$data = $data->fetchAll(PDO::FETCH_ASSOC);

		foreach ($data as $i => $boss) {
			$missed = 0;

			if ($data[$i]['id'] != 56 && $data[$i]['id'] != 57 && $data[$i]['id'] != 58 && $data[$i]['id'] != 60  && $data[$i]['id'] != 62 && $data[$i]['id'] != 36) {
				while ($data[$i]['next_spawn_' . $servercode] <= date('Y-m-d H:i:s')) {
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d H:i:s", strtotime($data[$i]['last_spawn_' . $servercode]) + intval(((++$missed) * $data[$i]['respawn']) * 3600));
				}
			} else if ($data[$i]['id'] == 56 || $data[$i]['id'] == 57 || $data[$i]['id'] == 62) {
				// Дерзость 1/3/5

				if (date("G", strtotime(date("Y-m-d H:i:s"))) < 13) {
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 13:00:00", strtotime("today"));
				} else if (date("G", strtotime(date("Y-m-d H:i:s"))) < 19) {
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 19:00:00", strtotime("today"));
				} else {
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 13:00:00", strtotime("tomorrow"));
				}
			} else if ($data[$i]['id'] == 58 || $data[$i]['id'] == 60) {
				// Дерзость 2/4
				if (date("G", strtotime(date("Y-m-d H:i:s"))) < 14)
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 14:00:00", strtotime("today"));
				else if (date("G", strtotime(date("Y-m-d H:i:s"))) < 20)
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 20:00:00", strtotime("today"));
				else
					$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 14:00:00", strtotime("tomorrow"));
			} else if ($data[$i]['id'] == 36) {
				// Селиходен
				$data[$i]['next_spawn_' . $servercode] = date("Y-m-d 22:00:00", strtotime("next wednesday"));
			}

			$connection->prepare("UPDATE `bosses` SET `next_spawn_{$servercode}`=? WHERE `id` =?")->execute(array($data[$i]['next_spawn_' . $servercode], $data[$i]['id']));
		}
	}

	die();
}

/* Бекенд логика для регистрации */
if ($_POST['action'] == "const") {
	if (empty($_POST['clans_ds'])) die("Поле `clan` не может быть пустым.");
	if (empty($_POST['classes_ds'])) die("Поле `class` не может быть пустым.");
	if (empty($_POST['servers_ds'])) die("Поле `server` не может быть пустым.");
	if (empty($_POST['nick'])) die("Поле `nick` не может быть пустым.");

	if (empty($_SESSION['dsk_id'])) die("Общая ошибка логина, перелогиньтесь.");
	else $_POST['dsk_id'] = $_SESSION['dsk_id'];

	if ($_POST['parties_new']) {
		$connection->prepare("INSERT IGNORE INTO `parties`(`name`, `server`) VALUES (?, ?);")->execute(array($_POST['parties_new'], $_POST['servers_ds']));
		$id = $connection->lastInsertId();

		if (!$id) die("КП с таким названием уже существует.");
		else {
			$connection->prepare("UPDATE `users` SET `lead` = 1, `clan` = ?, `party` = ? WHERE `dsk_id` = ?;")->execute(array($_POST['clans_ds'], $id, $_POST['dsk_id']));
		}
	}

	if ($_POST['parties_ds']) {
		$connection->prepare("UPDATE `users` SET `clan` = ?, `party` = ? WHERE `dsk_id` = ?;")->execute(array($_POST['clans_ds'], $_POST['parties_ds'], $_POST['dsk_id']));
	}

	if (empty($_POST['parties_new']) && empty($_POST['parties_ds'])) {
		$connection->prepare("UPDATE `users` SET `clan` = ? WHERE `dsk_id` = ?;")->execute(array($_POST['clans_ds'], $_POST['dsk_id']));
	}

	$data = $connection->prepare("SELECT CONCAT('[', `servers`.`code`, '] (', `clans`.`code`, ')') FROM `clans` JOIN `servers` ON `servers`.`id` = ? WHERE `clans`.`id` = ?;");
	$data->execute(array($_POST['servers_ds'], $_POST['clans_ds']));
	$_POST['name'] = $data->fetchColumn() . " " . $_POST['nick'];

	$data = $connection->prepare("SELECT `role_code` FROM `servers` WHERE `id` = ?;");
	$data->execute(array($_POST['servers_ds']));
	$_POST['servers_ds'] = $data->fetchColumn();

	$data = $connection->prepare("SELECT `role_code` FROM `roles` WHERE `name` = 'Авторизован';");
	$data->execute();
	$_POST['user_ds'] = $data->fetchColumn();

	$data = $connection->prepare("SELECT `role_code` FROM `roles` WHERE `name` = 'Зарегистрирован';");
	$data->execute();
	$_POST['user_reg_ds'] = $data->fetchColumn();

	$_POST['roles'] = $connection->query("SELECT `role_code` FROM `servers` UNION SELECT `role_code` FROM `classes`")->fetchAll(PDO::FETCH_COLUMN, 0);

	$data = sacredConnect('api', http_build_query($_POST));

	unset($_SESSION['dsk_id']);

	die($data);
}

/* Бекенд логика только для тех кто залогинен */

if (empty($_SESSION['user']['id'])) die("Общая ошибка логина, перелогиньтесь.");

if ($_POST['action'] == "pubevents") {
	if (empty($_POST['bosses'])) die("Поле `bosses` не может быть пустым.");
	if (empty($_POST['date'])) die("Поле `date` не может быть пустым.");
	if (empty($_POST['time'])) die("Поле `time` не может быть пустым.");

	$_POST['time'] = date("Y-m-d H:i:s", strtotime($_POST['date'] . " " . $_POST['time']));

	$bname = $connection->prepare("SELECT `name`, `icon` FROM `bosses` WHERE `id` = ?;");
	$bname->execute(array($_POST['bosses']));
	$bname = $bname->fetch(PDO::FETCH_ASSOC);

	$events = $connection->prepare("SELECT `time` FROM `events` WHERE `boss` = ? AND `server` = ? ORDER BY `time` DESC LIMIT 1;");
	$events->execute(array($_POST['bosses'], $_SESSION['user']['sid']));
	$events = $events->fetch(PDO::FETCH_ASSOC);

	if (round(abs(strtotime(date('Y-m-d H:i:s')) - strtotime($events['time'])) / 60) <= 120) die("Прошло менее двух часов с момента создания предыдущего события на этого босса.");

	if (empty($_POST['nokill'])) {
		$connection->prepare("INSERT INTO `events` (`boss`, `admin`, `awakened`, `server`, `close`) VALUES (?,?,?,?,?);")->execute(array($_POST['bosses'], $_SESSION['user']['id'], empty($_POST['awakened'])?0:1, $_SESSION['user']['sid'], $_POST['time']));
		$_POST['events'] = $connection->lastInsertId();

		$connection->prepare("UPDATE `events` JOIN `bosses` ON `bosses`.`id` = `events`.`boss` SET `bosses`.`last_spawn_{$_SESSION['user']['scode']}` = ? WHERE `events`.`id` = ?;")->execute(array($_POST['time'], $_POST['events']));

		$encryption = md5($_POST['events']);
		
		$bname['name'] = empty($_POST['awakened'])?$bname['name']:"Пробужденный ".$bname['name'];

		$dsMessage = "Событие **«" . $bname['name'] . "»** создано.\n Cоздатель: **«" . $_SESSION['user']['name'] . "»**!\n\n Открылось: " . date("Y-m-d H:i:s") . "\n Закроется: " . date("Y-m-d H:i:s", strtotime('+2 hours')) . "";
		dsSendMessageBoss($_SESSION['user']['sid'], $dsMessage, $encryption, "https://justdkp.com/img/icons/boss/{$bname['icon']}.png");

		foreach ($_POST['bdrop'] as $item) {
			$iname = $connection->prepare("SELECT `id`, `name`, `rarity`, `icon`, (SELECT `bosses`.`name` FROM `events` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` WHERE `events`.`id` = ?) AS `bname` FROM `items` WHERE `id` = ?");
			$iname->execute(array($_POST['events'], $item));
			$iname = $iname->fetch(PDO::FETCH_ASSOC);

			$connection->prepare("REPLACE INTO `loot` (`item`, `event`, `clan`, `admin`, `server`) VALUES (?,?,?,?,?)")->execute(array($item, $_POST['events'], $_POST['clans_ds'] ? $_POST['clans_ds'] : null, $_SESSION['user']['id'], $_SESSION['user']['sid']));
			$lid = $connection->lastInsertId();

			$wlist = $connection->prepare("SELECT *, 
				IF((SELECT COUNT(*) FROM `applicants` JOIN `loot` ON `loot`.`id` = `applicants`.`loot` WHERE (ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) BETWEEN 0 AND 720) AND `applicants`.`user` = `users`.`id` AND `status` <> 1) < 3, 1, 0) AS applylimit 
				FROM `wishlist` JOIN `users` ON `users`.`id` = `wishlist`.`user` WHERE `item` = ? AND `server` = ? HAVING applylimit = 1");

			$wlist->execute(array($iname['id'], $_SESSION['user']['sid']));
			$wlist = $wlist->fetch(PDO::FETCH_ASSOC);

			foreach ($wlist as $user) {
				$connection->prepare("REPLACE INTO `applicants`(`loot`, `server`, `clan`, `user`) VALUES (?,?,?,?)")->execute(array($lid, $wlist['server'], $wlist['clan'], $wlist['user']));
			}

			if ($iname['rarity'] == "Редкий") {
				$clan = $connection->prepare("SELECT `name` FROM `clans` WHERE `id` = ?");
				$clan->execute(array($_POST['clans_ds']));
				$clan = $clan->fetchColumn();

				$encryption = md5($lid);

				$dsMessage = "Предмет **«" . $iname['name'] . "»** выпавший из **«" . $iname['bname'] . "»** доступен для распределения, любой желающий может претендовать на него. Поднял клан: **«" . $clan . "»**";
				dsSendMessageLoot($_SESSION['user']['sid'], $dsMessage, $encryption, "https://justdkp.com/img/icons/loot/{$iname['icon']}.png");
			}
		}
	} else {
		$connection->prepare("INSERT INTO `events` (`boss`, `admin`, `server`, `close`, `status`, `checked_id`, `checked`) VALUES (?,?,?,?,1,1,1);")->execute(array($_POST['bosses'], $_SESSION['user']['id'], $_SESSION['user']['sid'], $_POST['time']));
		$_POST['events'] = $connection->lastInsertId();

		$connection->prepare("UPDATE `events` JOIN `bosses` ON `bosses`.`id` = `events`.`boss` SET `bosses`.`last_spawn_{$_SESSION['user']['scode']}` = ? WHERE `events`.`id` = ?;")->execute(array($_POST['time'], $_POST['events']));
	}

	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `admin`, `comment`, `dkp`) SELECT 'Bonus', `users`.`id`, `users`.`id`, 'Начислено <strong>20</strong> pts.</strong> за добавление информации на сайт!', 20 FROM `users` WHERE `users`.`id` = ?")->execute(array($_SESSION['user']['id']));

	userBonusUpdate($_SESSION['user']['sid']);
	lootUpdate();

	echo ('Success');

	die();
}

if ($_POST['action'] == "loot") {
	if (empty($_POST['loot'])) die("Поле `item` не может быть пустым.");
	if (empty($_POST['users'])) die("Поле `user` не может быть пустым.");

	$data = $connection->prepare("SELECT `id` FROM `loot` WHERE `status` = 0 AND `id` = ?;");
	$data->execute(array($_POST['loot']));
	$data = $data->fetch(PDO::FETCH_ASSOC);

	if (empty($data)) die("Кто-то уже распределил эту вещь до вас.");

	if (isset($_POST['salary'])) $salary = 1;
	else $salary = 0;

	$query = "SELECT `users`.`server` AS `sid`, `users`.`dkp_rating`, `users`.`id` AS `uid`, `users`.`dsk_id`, `users`.`name` AS `to_name`, `items`.`name` AS `item`, `items`.`icon` AS `icon`, `items`.`rarity` AS `rarity`, `items`.`uname` AS `from_name`, CEIL(`users`.`dkp`*(`items`.`dkp`/100)) AS price 
		FROM `users`
		JOIN (
			SELECT `items`.`dkp`, `items`.`name`, `items`.`rarity`, `items`.`icon`, `clans`.`name` AS uname 
			FROM `loot` 
			JOIN `clans` ON `loot`.`clan` = `clans`.`id` 
			JOIN `items` ON `loot`.`item` = `items`.`id` 
			WHERE `loot`.`id` = ?
		) `items` 
		WHERE `users`.`id` = ?;";

	$data = $connection->prepare($query);
	$data->execute(array($_POST['loot'], $_POST['users']));
	$data = $data->fetch(PDO::FETCH_ASSOC);

	$query = "SELECT GROUP_CONCAT(CONCAT('<@', `adsk_id`, '>') SEPARATOR ' ') FROM `drivers` WHERE `dsk_id` = ?;";

	$drivers = $connection->prepare($query);
	$drivers->execute(array($data['dsk_id']));
	$drivers = $drivers->fetchColumn();

	if ($salary) {
		$data['price'] = 0;
		$comment = "Вычтено <strong>{$data['price']} pts.</strong> за выкуп <strong>«{$data['item']}»</strong> в качестве зарплаты, <strong>«{$data['to_name']}»</strong> должен будет забрать предмет у КЛа клана <strong>«{$data['from_name']}»</strong>. Место в рейтинге на момент выдачи: <strong>«{$data['dkp_rating']}»</strong>!";
	} else {
		$data['price'] = 1;
		$comment = "Вычтено <strong>{$data['price']} pts.</strong> за выкуп <strong>«{$data['item']}»</strong>, <strong>«{$data['to_name']}»</strong> должен будет забрать предмет у КЛа клана <strong>«{$data['from_name']}»</strong>. Место в рейтинге на момент выдачи: <strong>«{$data['dkp_rating']}»</strong>!";
	}

	$connection->prepare("UPDATE `loot` JOIN `users` ON `users`.`id` = ?  SET `loot`.`points` = ?, `loot`.`rating` = `users`.`dkp_rating`, `loot`.`user` = `users`.`id`, `loot`.`admin` = ?, `loot`.`status` = 1, `loot`.`salary` = ?, `time` = NOW() WHERE `loot`.`id` = ?;")->execute(array($_POST['users'], $data['price'], $_SESSION['user']['id'], $salary, $_POST['loot']));
	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array('Transaction', $data['uid'], intval(-1 * $data['price']), $_SESSION['user']['id'], $comment));

	userDKPUpdate($_SESSION['user']['sid']);

	echo ('Success');

	die();
}

if ($_POST['action'] == "points") {
	if (empty($_POST['users'])) die("Поле `users` не может быть пустым.");
	if (empty(intval($_POST['points']))) die("Поле `points` не может быть пустым.");
	if (empty($_POST['comment'])) die("Поле `comment` не может быть пустым.");

	if ($_POST['points'] < 0) {
		$type = "Penalty";
		$comment = "Вычтено <strong>{$_POST['points']} pts.</strong> с комментарием: {$_POST['comment']}";
	}
	if ($_POST['points'] > 0) {
		$type = "Award";
		$comment = "Начислено <strong>{$_POST['points']} pts.</strong> с комментарием: {$_POST['comment']}";
	}

	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array($type, $_POST['users'], intval($_POST['points']), $_SESSION['user']['id'], $comment));

	if ($_POST['users_add'] && $_POST['points'] < 0) {
		$type = "Award";
		$_POST['points'] = abs($_POST['points']);
		$comment = "Начислено <strong>{$_POST['points']} pts.</strong> с комментарием: {$_POST['comment']}";

		$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array($type, $_POST['users_add'], intval($_POST['points']), $_SESSION['user']['id'], $comment));
	}

	userDKPUpdate($_SESSION['user']['sid']);
	lootUpdate();

	echo ('Success');

	die();
}

if ($_POST['action'] == "toevent") {
	if (empty($_POST['users'])) die("Поле `users` не может быть пустым.");
	if (empty($_POST['cevents'])) die("Поле `events` не может быть пустым.");

	$query = "SELECT `events`.`server`, `events`.`close`, `events`.`boss`, `events`.`pvp`, `bosses`.`name`, `bosses`.`dkp`, `users`.`server`, `users`.`clan`, `users`.`name` AS `uname` FROM `events` 
	JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
	JOIN `users` ON `users`.`id` = ?
	WHERE `events`.`id` = ? LIMIT 1";

	$event = $connection->prepare($query);
	$event->execute(array($_POST['users'], $_POST['cevents']));
	$event = $event->fetch(PDO::FETCH_ASSOC);

	$multiplier = 1;

	if (date("G", strtotime($event['close'])) >= 2 && date("G", strtotime($event['close'])) <= 7)
		$multiplier += 1;

	if ($event['pvp'])
		$multiplier += 1;

	$dkp = ceil($multiplier * $event['dkp']);
	$dkpm = $multiplier * 100;

	$comment = "Начислено <strong>{$dkp} pts.</strong> за убийство <strong>«{$event['name']}»</strong>";
	if (date("G", strtotime($event['close'])) >= 2 && date("G", strtotime($event['close'])) <= 7) $comment .= " во время ночного прайм-тайма";
	if ($event['pvp']) $comment .= " в условиях PVP";
	$comment .= "! [{$dkpm}%]";

	$event['close'] = date("d.m H:i", strtotime($event['close']));
	$connection->prepare("REPLACE INTO `points`(`user`, `event`, `admin`, `dkp`, `multiplier`, `comment`) VALUES (?,?,?,?,?,?)")->execute(array($_POST['users'], $_POST['cevents'], $_SESSION['user']['id'], $dkp, $multiplier, $comment));
	$connection->prepare("REPLACE INTO `attendance`(`event`, `boss`, `server`, `clan`, `user`) VALUES (?,?,?,?,?)")->execute(array($_POST['cevents'], $event['boss'], $event['server'], $event['clan'], $_POST['users']));
	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array("Award", $_SESSION['user']['id'], 10, $_SESSION['user']['id'], "Начислено <strong>10</strong> pts. за внесение <strong>«{$event['uname']}»</strong> в событие <strong>«{$event['name']} ({$event['close']})»</strong>!"));

	userDKPUpdate($_SESSION['user']['sid']);
	userBossUpdate($_SESSION['user']['sid']);
	lootUpdate();

	echo ('Success');

	die();
}

if ($_POST['action'] == "fromevent") {
	if (empty($_POST['users'])) die("Поле `users` не может быть пустым.");
	if (empty($_POST['users_add'])) die("Поле `users` не может быть пустым.");
	if (empty($_POST['cevents'])) die("Поле `events` не может быть пустым.");

	$connection->prepare("DELETE FROM `attendance` WHERE `event` = ? AND `user` = ?")->execute(array($_POST['cevents'], $_POST['users']));
	$connection->prepare("DELETE FROM `points` WHERE `event` = ? AND `user` = ?")->execute(array($_POST['cevents'], $_POST['users']));

	$event = $connection->prepare("SELECT `bosses`.`name`, `events`.`close` FROM `events` JOIN `bosses` ON `bosses`.`id` = `events`.`boss` WHERE `events`.`id` = ?");
	$event->execute(array($_POST['cevents']));
	$event = $event->fetch(PDO::FETCH_ASSOC);
	$event['close'] = date("d.m H:i", strtotime($event['close']));

	$user = $connection->prepare("SELECT `dkp`, `name` FROM `users` WHERE `id` = ?");
	$user->execute(array($_POST['users']));
	$user = $user->fetch(PDO::FETCH_ASSOC);
	$user['dkp'] = ceil($user['dkp'] * 0.01);

	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array("Award", $_POST['users_add'], $user['dkp'], $_SESSION['user']['id'], "Начислено <strong>{$user['dkp']}</strong> pts. за удаление <strong>«{$user['name']}»</strong> из события <strong>«{$event['name']} ({$event['close']})»</strong>!"));
	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array("Penalty", $_POST['users'], $user['dkp'], $_SESSION['user']['id'], "Вычтено <strong>{$user['dkp']}</strong> pts. за удаление из события <strong>«{$event['name']} ({$event['close']})»</strong>!"));

	userDKPUpdate($_SESSION['user']['sid']);
	userBossUpdate($_SESSION['user']['sid']);
	lootUpdate();

	echo ('Success');

	die();
}

if ($_POST['action'] == "pointsall") {
	if (empty($_POST['users_all'])) die("Поле `users` не может быть пустым.");
	if (empty(intval($_POST['points']))) die("Поле `points` не может быть пустым.");
	if (empty($_POST['comment'])) die("Поле `comment` не может быть пустым.");

	if ($_POST['points'] < 0) {
		$type = "Penalty";
		$comment = "Вычтено <strong>{$_POST['points']} pts.</strong> с комментарием: {$_POST['comment']}";
	}
	if ($_POST['points'] > 0) {
		$type = "Award";
		$comment = "Начислено <strong>{$_POST['points']} pts.</strong> с комментарием: {$_POST['comment']}";
	}

	$connection->prepare("REPLACE INTO `points`(`type`, `user`, `dkp`, `admin`, `comment`) VALUES (?,?,?,?,?)")->execute(array($type, $_POST['users_all'], intval($_POST['points']), $_SESSION['user']['id'], $comment));

	echo ('Success');

	die();
}

if ($_POST['action'] == "data") {
	if (empty($_POST['level']) || !is_numeric($_POST['level'])) $_POST['level'] = 60;
	if (empty($_POST['collections']) || !is_numeric($_POST['collections'])) $_POST['collections'] = 0;
	if (empty($_POST['heroes_all']) || !is_numeric($_POST['heroes_all'])) $_POST['heroes_all'] = 0;
	if (empty($_POST['agations_all']) || !is_numeric($_POST['agations_all'])) $_POST['agations_all'] = 0;
	if (empty($_POST['heroes_hr']) || !is_numeric($_POST['heroes_hr'])) $_POST['heroes_hr'] = 0;
	if (empty($_POST['heroes_lg']) || !is_numeric($_POST['heroes_lg'])) $_POST['heroes_lg'] = 0;
	if (empty($_POST['agations_hr']) || !is_numeric($_POST['agations_hr'])) $_POST['agations_hr'] = 0;
	if (empty($_POST['agations_lg']) || !is_numeric($_POST['agations_lg'])) $_POST['agations_lg'] = 0;
	if (empty($_POST['defence']) || !is_numeric($_POST['defence'])) $_POST['defence'] = 0;
	if (empty($_POST['reduction']) || !is_numeric($_POST['reduction'])) $_POST['reduction'] = 0;
	if (empty($_POST['resistance']) || !is_numeric($_POST['resistance'])) $_POST['resistance'] = 0;
	if (empty($_POST['seal']) || !is_numeric($_POST['seal'])) $_POST['seal'] = 0;
	if (empty($_POST['prime']) || !is_numeric($_POST['prime'])) $_POST['prime'] = 0;
	if (empty($_POST['awakening']) || !is_numeric($_POST['awakening'])) $_POST['awakening'] = 0;
	if (empty($_POST['souls']) || !is_numeric($_POST['souls'])) $_POST['souls'] = 0;

	if (isset($_POST['ready'])) $ready = 1;
	else $ready = 0;

	$connection->prepare("UPDATE `users` SET `level`=?, `heroes_all`=?,`agations_all`=?,`agations_hr`=?,
	`agations_lg`=?,`heroes_hr`=?,`heroes_lg`=?,`collections`=?,`defence`=?,`reduction`=?,`resistance`=?,`seal`=?,`prime`=?,`awakening`=?,`souls`=?,`ready`=? 
	WHERE id = ?")->execute(array(
		$_POST['level'], $_POST['heroes_all'], $_POST['agations_all'], $_POST['agations_hr'],
		$_POST['agations_lg'], $_POST['heroes_hr'], $_POST['heroes_lg'], $_POST['collections'],
		$_POST['defence'], $_POST['reduction'], $_POST['resistance'], $_POST['seal'], $_POST['prime'], $_POST['awakening'], $_POST['souls'],
		$ready, $_SESSION['user']['id']
	));

	userCardUpdate($_SESSION['user']['sid']);
	userColUpdate($_SESSION['user']['sid']);
	userPSUpdate($_SESSION['user']['sid']);
	userDKPUpdate($_SESSION['user']['sid']);
	userBossUpdate($_SESSION['user']['sid']);
	userBonusUpdate($_SESSION['user']['sid']);
	PSUpdate($_SESSION['user']['id']);
	lootUpdate();

	echo ('Success');

	die();
}

if ($_POST['action'] == "private") {
	$connection->prepare("DELETE FROM `holdings` WHERE `user` = ?")->execute(array($_SESSION['user']['id']));
	foreach ($_POST['pitems'] as $item) {
		$connection->prepare("REPLACE INTO `holdings`(`user`, `item`) VALUES (?,?)")->execute(array($_SESSION['user']['id'], $item));
	}

	$connection->prepare("DELETE FROM `skills` WHERE `user` = ?")->execute(array($_SESSION['user']['id']));
	foreach ($_POST['pskills'] as $skill) {
		$connection->prepare("REPLACE INTO `skills`(`user`, `item`) VALUES (?,?)")->execute(array($_SESSION['user']['id'], $skill));
	}

	echo ('Success');

	die();
}

if ($_POST['action'] == "wishlist") {
	$connection->prepare("DELETE FROM `wishlist` WHERE `user` = ?")->execute(array($_SESSION['user']['id']));
	foreach ($_POST['ritems'] as $item) {
		$connection->prepare("REPLACE INTO `wishlist`(`user`, `item`) VALUES (?,?)")->execute(array($_SESSION['user']['id'], $item));
	}

	echo ('Success');

	die();
}

if ($_POST['action'] == "pvp") {
	if (empty($_POST['pvpusers'])) die("Поле `users` не может быть пустым.");

	$data = $connection->prepare("SELECT `rating_pvp` FROM `users` WHERE `id` = ?;");
	$data->execute(array($_POST['pvpusers']));
	$loser_rating = $data->fetchColumn();

	$data = $connection->prepare("SELECT `rating_pvp` FROM `users` WHERE `id` = ?;");
	$data->execute(array($_SESSION['user']['id']));
	$winner_rating = $data->fetchColumn();

	$connection->prepare("UPDATE `users` SET `rating_pvp` = `rating_pvp`+1 WHERE `rating_pvp` BETWEEN ? AND ? AND `class` = ?")->execute(array($loser_rating, $winner_rating - 1, $_SESSION['user']['class']));
	$connection->prepare("UPDATE `users` SET `rating_pvp` = ? WHERE `id` = ?")->execute(array($loser_rating, $_SESSION['user']['id']));

	$connection->prepare("REPLACE INTO `pvp`(`class`, `winner`, `winner_rating`, `loser`, `loser_rating`) VALUES (?,?,?,?,?)")->execute(array($_SESSION['user']['class'], $_SESSION['user']['id'], $loser_rating, $_POST['pvpusers'], $loser_rating + 1));

	echo ('Success');

	die();
}

if ($_POST['action'] == "changeuser") {
	if (empty($_POST['clans_ds'])) die("Поле `clan` не может быть пустым.");
	if (empty($_POST['rloot'])) die("Поле `loot` не может быть пустым.");

	$connection->prepare("UPDATE `loot` SET `clan`= ? WHERE `id` = ?")->execute(array($_POST['clans_ds'], $_POST['rloot']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "changeres") {
	if (empty($_POST['users'])) die("Поле `user` не может быть пустым.");
	if (empty($_POST['aloot'])) die("Поле `loot` не может быть пустым.");

	$connection->prepare("UPDATE `loot` SET `user`= ? WHERE `id` = ?")->execute(array($_POST['users'], $_POST['aloot']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "dropadd") {
	if (empty($_POST['cevents'])) die("Поле `events` не может быть пустым.");
	if (empty($_POST['drop'])) die("Поле `drop` не может быть пустым.");
	if (empty($_POST['clans_ds'])) die("Поле `clan` не может быть пустым.");

	foreach ($_POST['drop'] as $item) {
		$iname = $connection->prepare("SELECT `id`, `name`, `rarity`, `icon`, (SELECT `bosses`.`name` FROM `events` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` WHERE `events`.`id` = ?) AS `bname` FROM `items` WHERE `id` = ?");
		$iname->execute(array($_POST['cevents'], $item));
		$iname = $iname->fetch(PDO::FETCH_ASSOC);

		$connection->prepare("REPLACE INTO `loot` (`item`, `event`, `clan`, `admin`, `server`) VALUES (?,?,?,?,?)")->execute(array($item, $_POST['cevents'], $_POST['clans_ds'] ? $_POST['clans_ds'] : null, $_SESSION['user']['id'], $_SESSION['user']['sid']));
		$lid = $connection->lastInsertId();

		$wlist = $connection->prepare("SELECT *, 
		IF((SELECT COUNT(*) FROM `applicants` JOIN `loot` ON `loot`.`id` = `applicants`.`loot` WHERE (ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) BETWEEN 0 AND 720) AND `applicants`.`user` = `users`.`id` AND `status` <> 1) < 3, 1, 0) AS applylimit 
		FROM `wishlist` JOIN `users` ON `users`.`id` = `wishlist`.`user` WHERE `item` = ? AND `server` = ? HAVING applylimit = 1");

		$wlist->execute(array($iname['id'], $_SESSION['user']['sid']));
		$wlist = $wlist->fetch(PDO::FETCH_ASSOC);

		foreach ($wlist as $user) {
			$connection->prepare("REPLACE INTO `applicants`(`loot`, `server`, `clan`, `user`) VALUES (?,?,?,?)")->execute(array($lid, $wlist['server'], $wlist['clan'], $wlist['user']));
		}

		if ($iname['rarity'] == "Редкий") {
			if ($_POST['clans_ds']) {
				$clan = $connection->prepare("SELECT `name` FROM `clans` WHERE `id` = ?");
				$clan->execute(array($_POST['clans_ds']));
				$clan = $clan->fetchColumn();
			}

			$encryption = md5($lid);

			$dsMessage = "Предмет **«" . $iname['name'] . "»** выпавший из **«" . $iname['bname'] . "»** доступен для распределения, любой желающий может претендовать на него. Поднял клан: **«" . $clan . "»**";
			dsSendMessageLoot($_SESSION['user']['sid'], $dsMessage, $encryption, "https://justdkp.com/img/icons/loot/{$iname['icon']}.png");
		}
	}

	echo ('Success');

	die();
}

if ($_POST['action'] == "addclan") {
	if (empty($_POST['clan'])) die("Поле `clan` не может быть пустым.");
	if (empty($_POST['code'])) die("Поле `code` не может быть пустым.");
	if (empty($_POST['servers_ds'])) die("Поле `servers` не может быть пустым.");

	$connection->prepare("INSERT IGNORE INTO `clans`(`name`, `code`, `server`) VALUES (?, ?, ?)")->execute(array($_POST['clan'], $_POST['code'], $_POST['servers_ds']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "removeclan") {
	if (empty($_POST['clans_all'])) die("Поле `clan` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `clan` = NULL WHERE `clan` = ?")->execute(array($_POST['clans_all']));
	$connection->prepare("DELETE FROM `clans` WHERE `id` = ?")->execute(array($_POST['clans_all']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "createcp") {
	if (empty($_POST['cpn'])) die("Поле `name` не может быть пустым.");
	if (empty($_POST['servers_ds'])) die("Поле `servers` не может быть пустым.");

	$connection->prepare("INSERT IGNORE INTO `parties`(`name`, `server`) VALUES (?, ?);")->execute(array($_POST['cpn'], $_POST['servers_ds']));
	$id = $connection->lastInsertId();

	if (!$id)
		echo ("КП с таким названием уже существует.");
	else
		echo ('Success');

	die();
}

if ($_POST['action'] == "addcp") {
	if (empty($_POST['users'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `party` = ? WHERE `id` = ?")->execute(array($_SESSION['user']['party'], $_POST['users']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "addcpall") {
	if (empty($_POST['users_all'])) die("Поле `users` не может быть пустым.");
	if (empty($_POST['parties_all'])) die("Поле `parties` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `party` = ? WHERE `id` = ?")->execute(array($_POST['parties_all'], $_POST['users_all']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "remsoul") {
	if (empty($_POST['uitems'])) die("Поле `soul` не может быть пустым.");
	if (empty($_POST['users'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("DELETE FROM `souls` WHERE `user` = ? AND `soul` = ?")->execute(array($_POST['users'], $_POST['uitems']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "delcpall") {
	if (empty($_POST['parties_all'])) die("Поле `parties` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `party` = NULL WHERE `party` = ?")->execute(array($_POST['parties_all']));
	$connection->prepare("DELETE FROM `parties` WHERE `id` = ?")->execute(array($_POST['parties_all']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "remcp") {
	if (empty($_POST['users_cp'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `party` = NULL WHERE `id` = ?")->execute(array($_POST['users_cp']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "remcpall") {
	if (empty($_POST['users_all'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `party` = NULL WHERE `id` = ?")->execute(array($_POST['users_all']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "rencp") {
	if (empty($_POST['cpn'])) die("Поле `name` не может быть пустым.");

	$data = $connection->prepare("SELECT `id` FROM `parties` WHERE `name` = ?");
	$data->execute(array($_POST['cpn']));
	$duplicate = $data->fetchColumn();

	if ($duplicate) die("КП с таким названием уже существует.");
	else {
		$connection->prepare("UPDATE `parties` SET `name` = ? WHERE `id` = ?")->execute(array($_POST['cpn'], $_SESSION['user']['party']));
	}

	echo ('Success');

	die();
}

if ($_POST['action'] == "movecp") {
	if (empty($_POST['parties'])) die("Поле `parties` не может быть пустым.");
	if (empty($_POST['servers_ds'])) die("Поле `servers` не может быть пустым.");
	if (empty($_POST['clans_ds'])) die("Поле `clans` не может быть пустым.");

	$connection->prepare("UPDATE `parties` SET `server` = ? WHERE `id` = ?")->execute(array($_POST['servers_ds'], $_POST['parties']));
	$connection->prepare("UPDATE `users` SET `server` = ?, `clan` = ? WHERE `party` = ?")->execute(array($_POST['servers_ds'], $_POST['clans_ds'], $_POST['parties']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "nullrating") {
	if (empty($_POST['users'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `rating_pvp` = 99 WHERE `id` = ?")->execute(array($_POST['users']));;

	$connection->prepare("
		UPDATE `users` a
		INNER JOIN (
			SELECT (@row_number := @row_number + 1) AS rating, users.*
			FROM
			(
				SELECT `users`.`id`, `rating_pvp` FROM `users` WHERE `users`.`class` = (SELECT `users`.`class` FROM `users` WHERE `users`.`id` = ?) AND `rating_pvp` <> 99
				GROUP BY `users`.`id` ORDER BY `rating_pvp` ASC 
			) users
			CROSS JOIN (SELECT @row_number := 0) AS ranking	
		) b ON a.`id` = b.`id`
		SET a.`rating_pvp` = b.`rating`
		WHERE a.`id` = b.`id`
	")->execute(array($_POST['users']));;

	echo ('Success');

	die();
}

if ($_POST['action'] == "remdrop") {
	if (empty($_POST['floot'])) die("Поле `loot` не может быть пустым.");

	$iname = $connection->prepare("SELECT `name`, `rarity` FROM `loot` JOIN `items` ON `items`.`id`=`loot`.`item` WHERE `loot`.`id` = ?");
	$iname->execute(array($_POST['floot']));
	$iname = $iname->fetch(PDO::FETCH_ASSOC);

	$connection->prepare("DELETE FROM `loot` WHERE `id` =  ?")->execute(array($_POST['floot']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "bossloot") {
	if (empty($_POST['items'])) die("Поле `items` не может быть пустым.");
	if (empty($_POST['bosses'])) die("Поле `bosses` не может быть пустым.");

	$connection->prepare("REPLACE INTO `droplist`(`boss`, `item`) VALUES (?, ?)")->execute(array($_POST['bosses'], $_POST['items']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "ban") {
	if (empty($_POST['users'])) die("Поле `user` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `server`=NULL,`clan`=NULL,`party`=NULL WHERE `id` = ?")->execute(array($_POST['users']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "remevent") {
	if (empty($_POST['events'])) die("Поле `events` не может быть пустым.");

	$connection->prepare("DELETE FROM `events` WHERE `id` =  ?")->execute(array($_POST['events']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "timezone") {
	if (empty($_POST['timezone'])) die("Поле `timezone` не может быть пустым.");

	$connection->prepare("UPDATE `users` SET `timezone` = ? WHERE `id` = ?")->execute(array($_POST['timezone'], $_SESSION['user']['id']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "delevent") {
	if (empty($_POST['cevents'])) die("Поле `events` не может быть пустым.");

	$connection->prepare("DELETE FROM `events` WHERE `id` =  ?")->execute(array($_POST['cevents']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "delete") {
	if (empty($_POST['servers_ds'])) die("Поле `servers` не может быть пустым.");

	if ($_POST['cp_delete'] == "On") {
		$connection->prepare("DELETE FROM `parties` WHERE `server` = ?")->execute(array($_POST['servers_ds']));
	}
	if ($_POST['clan_delete'] == "On") {
		$connection->prepare("DELETE FROM `clans` WHERE `server` = ?")->execute(array($_POST['servers_ds']));
	}
	if ($_POST['boss_delete'] == "On") {
		$connection->prepare("DELETE FROM `attendance` WHERE `server` = ?")->execute(array($_POST['servers_ds']));
	}
	if ($_POST['dkp_delete'] == "On") {
		$users = $connection->prepare("SELECT GROUP_CONCAT(`id`) FROM `users` WHERE `server` = ?");
		$users->execute(array($_POST['servers_ds']));
		$users = $users->fetchColumn();

		$connection->prepare("DELETE `points` FROM `points` WHERE `user` IN ($users)")->execute();
	}
	if ($_POST['ppl_delete'] == "On") {
		$connection->prepare("UPDATE `users` SET `server` = NULL, `clan` = NULL, `class` = NULL, `party` = NULL WHERE `server` = ?")->execute(array($_POST['servers_ds']));
	}

	echo ('Success');

	die();
}

if ($_POST['action'] == "adddraiv") {
	if (empty($_POST['users_all'])) die("Поле `users` не может быть пустым.");
	if (empty($_POST['users_all_add'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("INSERT INTO `drivers` (`dsk_id`, `adsk_id`) VALUES ((SELECT `dsk_id` FROM `users` WHERE `id` = ?), (SELECT `dsk_id` FROM `users` WHERE `id` = ?));")->execute(array($_POST['users_all'], $_POST['users_all_add']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "additem") {
	if (empty($_POST['name'])) die("Поле `name` не может быть пустым.");
	if (empty($_POST['itemrarity'])) die("Поле `rarity` не может быть пустым.");
	if (empty($_POST['itemtype'])) die("Поле `type` не может быть пустым.");
	if (empty($_POST['icons'])) die("Поле `icons` не может быть пустым.");

	if($_POST['itemrarity'] = "Редкий") $dkp = 20;
	else $dkp = 1;

	$connection->prepare("INSERT INTO `items`(`name`, `rarity`, `dkp`, `type`, `class`, `icon`) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE `rarity`=?, `dkp`=?, `type`=?, `class`=?, `icon`=?")->execute(array($_POST['name'], $_POST['itemrarity'], $dkp, $_POST['itemtype'], $_POST['itemclass']?$_POST['itemclass']:null, $_POST['icons'], $_POST['itemrarity'], $dkp, $_POST['itemtype'], $_POST['itemclass']?$_POST['itemclass']:null, $_POST['icons']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "remdraiv") {
	if (empty($_POST['users_dr'])) die("Поле `users` не может быть пустым.");

	$connection->prepare("DELETE FROM `drivers` WHERE `drivers`.`adsk_id` = ?")->execute(array($_POST['users_dr']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "timechange") {
	if (empty($_POST['cevents'])) die("Поле `events` не может быть пустым.");

	$_POST['time'] = date("Y-m-d H:i:s", strtotime($_POST['date'] . " " . $_POST['time']));
	$connection->prepare("UPDATE `events` JOIN `bosses` ON `bosses`.`id` = `events`.`boss` SET `bosses`.`last_spawn_{$_SESSION['user']['scode']}` = ?, `events`.`close` = ? WHERE `events`.`id` = ?;")->execute(array($_POST['time'], $_POST['time'], $_POST['cevents']));

	echo ('Success');

	die();
}

if ($_POST['action'] == "restart") {
	$_POST['time'] = date("Y-m-d H:i:s", strtotime($_POST['date'] . " " . $_POST['time']));

	$data = $connection->prepare("SELECT COUNT(*) FROM `bosses` WHERE `last_spawn_E1` <= ?");
	$data->execute(array($_POST['time']));
	$data = $data->fetchColumn();

	if (empty($data)) die("Кто-то уже ввел время рестарта серверов до вас.");

	$connection->prepare("UPDATE `bosses` SET `last_spawn_B1`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_B1`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_B2`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_B2`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_B3`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_B3`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_B4`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_B4`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_B5`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_B5`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_B6`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_B6`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_Z1`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_Z1`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_Z2`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_Z2`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_Z3`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_Z3`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_Z4`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_Z4`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_Z5`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_Z5`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_Z6`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_Z6`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_E1`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_E1`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_E2`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_E2`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_E3`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_E3`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_E4`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_E4`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_E5`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_E5`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_E6`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_E6`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_L1`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_L1`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_L2`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_L2`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_L3`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_L3`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_L4`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_L4`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_L5`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_L5`=DATE_ADD(?, INTERVAL `restart` HOUR), `last_spawn_L6`=DATE_ADD(?, INTERVAL `restart` HOUR), `next_spawn_L6`=DATE_ADD(?, INTERVAL `restart` HOUR) WHERE `id` NOT IN (36,56,57,58,60,62)")->execute(array_fill(0, 24, $_POST['time']));

	echo ('Success');

	die();
}

/* Прочий функционал */

if ($_POST['action'] == "imgupload") {
	function convertImage($originalImage, $outputImage, $quality)
	{
		include_once("functions.php");

		switch (exif_imagetype($originalImage)) {
			case IMAGETYPE_PNG:
				$imageTmp = imagecreatefrompng($originalImage);
				break;
			case IMAGETYPE_JPEG:
				$imageTmp = imagecreatefromjpeg($originalImage);
				break;
			case IMAGETYPE_BMP:
				$imageTmp = imagecreatefrombmp($originalImage);
				break;
			default:
				$imageTmp = imagecreatefromjpeg($originalImage);
				break;
		}

		$res = imagejpeg($imageTmp, $outputImage, $quality);
		imagedestroy($imageTmp);

		return $res;
	}

	if ($_FILES['file']['size'] < (1024 * 1024 * 10)) {
		$file = $_SERVER['DOCUMENT_ROOT'] . '/img/confirmations/' . $_POST['id'] . '.jpg';
		$res = convertImage($_FILES['file']['tmp_name'], $file, 75);

		if ($res) {
			$data = $connection->prepare("UPDATE `loot` SET `received` = 1 WHERE`id` = ?");
			$data->execute(array($_POST['id']));
			die('Success');
		} else
			die('При загрузке данных произошла ошибка.');
	} else {
		die('Файл должен весить меньше 10 MB.');
	}
}

if ($_POST['action'] == "im") {
	$file = '/img/confirmations/' . $_POST['id'] . '.jpg';

	if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) {
		$message = '<img src="' . $file . '" style="max-width: -webkit-fill-available;">';
	} else {
		$message = "Подтверждение о получении для этого предмета не получено.";
	}


	die($message);
}

if ($_POST['action'] == "pp") {
	if ($_POST['status'] == 'true') $_POST['status'] = 1;
	else $_POST['status'] = 0;

	$data = $connection->prepare("UPDATE `events` SET `pvp` = ?, `pvp_id` = ? WHERE `id` = ?");
	$data->execute(array($_POST['status'], $_SESSION['user']['id'], $_POST['id']));

	if ($_POST['status'])
		die("Событие отмечено как PVP-событие");
	else
		die("Событие больше не является PVP-событием");
}

if ($_POST['action'] == "ec") {
	$already = $connection->prepare("SELECT `checked` FROM `events` WHERE `id` = ?;");
	$already->execute(array($_POST['id']));
	$already = $already->fetchColumn();

	if ($already) {
		die("Другой модератор уже проверил эту информацию");
	} else {
		$data = $connection->prepare("UPDATE `events` SET `checked` = 1, `checked_id` = ? WHERE `id` = ?");
		$data->execute(array($_SESSION['user']['id'], $_POST['id']));

		$connection->prepare("REPLACE INTO `points`(`type`, `user`, `admin`, `comment`, `dkp`) SELECT 'Bonus', `users`.`id`, `users`.`id`, 'Начислено <strong>40</strong> pts.</strong> за проверку информации о посещаемости события!', 40 FROM `users` WHERE `users`.`id` = ?")->execute(array($_SESSION['user']['id']));

		die("Начислено 40pts. за проверку информации о посещаемости события!");
	}
}

if ($_POST['action'] == "so") {
	$data = $connection->prepare("REPLACE INTO `souls`(`user`, `soul`) VALUES (?,?)");
	$data->execute(array($_POST['id'], $_POST['uid']));

	die("Душа добавлена!");
}

if ($_POST['action'] == "lc") {
	$already = $connection->prepare("SELECT `checked` FROM `loot` WHERE `id` = ?;");
	$already->execute(array($_POST['id']));
	$already = $already->fetchColumn();

	if ($already) {
		die("Другой модератор уже проверил эту информацию");
	} else {
		$data = $connection->prepare("UPDATE `loot` SET `checked` = 1, `checked_id` = ? WHERE `id` = ?");
		$data->execute(array($_SESSION['user']['id'], $_POST['id']));

		$connection->prepare("REPLACE INTO `points`(`type`, `user`, `admin`, `comment`, `dkp`) SELECT 'Bonus', `users`.`id`, `users`.`id`, 'Начислено <strong>10</strong> pts.</strong> за проверку информации о полученном луте!', 10 FROM `users` WHERE `users`.`id` = ?")->execute(array($_SESSION['user']['id']));

		die("Начислено 10pts. за проверку информации о полученном луте!");
	}
}

if ($_POST['action'] == "cl") {
	if ($_POST['status'] == 'false') $_POST['status'] = 0;
	if ($_POST['status'] == 'true') $_POST['status'] = 1;

	$data = $connection->prepare("UPDATE `loot` SET `received` = ? WHERE `id` = ?");
	$data->execute(array($_POST['status'], $_POST['id']));

	die('Success');
}

if ($_POST['action'] == "ae") {
	if ($_POST['status'] == 'remove') {
		$data = $connection->prepare("DELETE FROM `attendance` WHERE `event` = ? AND `user` = ?");
		$data->execute(array($_POST['id'], $_SESSION['user']['id']));
	} else if ($_POST['status'] == 'add') {
		$data = $connection->prepare("REPLACE INTO `attendance`(`event`, `boss`, `server`, `clan`, `user`) SELECT `events`.`id`, `events`.`boss`, `users`.`server`, `users`.`clan`, `users`.`id` FROM `users` LEFT JOIN `events` ON `events`.`id` = ? WHERE `users`.`id` = ?");
		$data->execute(array($_POST['id'], $_SESSION['user']['id']));
	}

	die('Success');
}

if ($_POST['action'] == "ua") {
	$data = $connection->prepare("SELECT GROUP_CONCAT(`users`.`name` ORDER BY `users`.`class`) AS names FROM `attendance` LEFT JOIN `users` ON `attendance`.`user` = `users`.`id` WHERE `event` = ?");
	$data->execute(array($_POST['id']));
	$data = $data->fetch(PDO::FETCH_ASSOC);

	echo ($data['names'] ? str_replace(",", "<br>", $data['names']) : "");

	die();
}

if ($_POST['action'] == "dl") {
	$data = $connection->prepare("SELECT GROUP_CONCAT(`items`.`name`) AS names FROM `droplist` LEFT JOIN `items` ON `droplist`.`item` = `items`.`id` WHERE `boss` = ? ORDER BY `items`.`name`");
	$data->execute(array($_POST['id']));
	$data = $data->fetch(PDO::FETCH_ASSOC);

	echo ($data['names'] ? str_replace(",", "<br>", $data['names']) : "");

	die();
}

if ($_POST['action'] == "ll") {
	$data = $connection->prepare("SELECT GROUP_CONCAT(CONCAT(`users`.`name`, ' (#', `users`.`dkp_rating`, ')')) AS names FROM `applicants` LEFT JOIN `users` ON `applicants`.`user` = `users`.`id` WHERE `loot` = ? ORDER BY `users`.`dkp_rating`");
	$data->execute(array($_POST['id']));
	$data = $data->fetch(PDO::FETCH_ASSOC);

	echo ($data['names'] ? str_replace(",", "<br>", $data['names']) : "");

	die();
}

if ($_POST['action'] == "is") {
	$text = "<h5>Способности:</h5>";

	$data = $connection->prepare("SELECT * FROM `items` WHERE `id` IN (SELECT `item` FROM (SELECT `user`, `item`  FROM `skills` UNION SELECT `user`, `item` FROM `holdings`) a WHERE `user` = ?) AND `type` = 'Скилл' AND `class` = (SELECT `class` FROM `users` WHERE `id` = ?)");
	$data->execute(array($_POST['id'], $_POST['id']));

	$temp = "";
	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$temp .= "<strong style='color: $color;'>" . $row['name'] . "</strong><br>";
	}

	if (empty($temp)) $text .= "~";
	else $text .= $temp;

	$text .= "<br><h5>Героическое и легендарное снаряжение:</h5>";

	$data = $connection->prepare("SELECT * FROM `items` WHERE `id` IN (SELECT `item` FROM (SELECT `user`, `item`  FROM `skills` UNION SELECT `user`, `item` FROM `holdings`) a WHERE `user` = ?) AND `type` IS NOT NULL AND `type` <> 'Скилл' AND `rarity` <> 'Редкий'");
	$data->execute(array($_POST['id']));

	$temp = "";
	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$temp .= "<strong style='color: $color;'>" . $row['name'] . "</strong><br>";
	}

	if (empty($temp)) $text .= "~";
	else $text .= $temp;

	echo ($text ? $text : "");

	die();
}

die();
