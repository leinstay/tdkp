<?php
include_once("config.php");

header('Content-Type: application/json; charset=utf-8');

if ($_GET['g'] == "bosses") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `bosses`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "events") {
	$result = array();
	foreach ($connection->query("SELECT `events`.`id`, `events`.`time`, `bosses`.`name` FROM `events` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` WHERE `status` = 0 AND `events`.`server` = {$_SESSION['user']['sid']} ORDER BY `events`.`time` DESC") as $data) {
		$result[] = array('value' => $data['id'], 'label' => date("d.m H:i", strtotime($data['time'])) . ' | ' . $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "cevents") {
	$result = array();
	foreach ($connection->query("SELECT `events`.`id`, `events`.`time`, `bosses`.`name` FROM `events` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` WHERE `events`.`server` = {$_SESSION['user']['sid']} ORDER BY `events`.`time` DESC LIMIT 100") as $data) {
		$result[] = array('value' => $data['id'], 'label' => date("d.m H:i", strtotime($data['time'])) . ' | ' . $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "users" || $_GET['g'] == "users_add") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `users` WHERE `server` = {$_SESSION['user']['sid']} OR `id` = 1 OR `id` = 2") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}
if ($_GET['g'] == "users_dr") {
	$result = array();
	foreach ($connection->query("SELECT `users`.`dsk_id`, `name` FROM `drivers` LEFT JOIN `users` ON `drivers`.`adsk_id` = `users`.`dsk_id`") as $data) {
		$result[] = array('value' => $data['dsk_id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "users_all" || $_GET['g'] == "users_all_add") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `users` WHERE `id` <> 1 AND `id` <> 2 AND `server` IS NOT NULL AND `class` IS NOT NULL AND `clan` IS NOT NULL ORDER BY `name`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "users_cp") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `users` WHERE `party` = {$_SESSION['user']['party']} AND `id` <> {$_SESSION['user']['id']}") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "pvpusers") {
	$result = array();

	$array = $connection->prepare("SELECT * FROM (SELECT `id`, `name`, `rating_pvp`, cuser.srating FROM `users` JOIN (SELECT `rating_pvp` AS srating FROM `users` WHERE `id` = ? LIMIT 1) cuser WHERE `id` <> 1 AND `class` = ? AND `rating_pvp` < srating ORDER BY `rating_pvp` DESC LIMIT 1) AS a ORDER BY `rating_pvp` ASC");
	$array->execute(array($_SESSION['user']['id'], $_SESSION['user']['class']));
	$array = $array->fetchAll(PDO::FETCH_ASSOC);

	foreach ($array as $data) {
		$result[] = array('value' => $data['id'], 'label' =>  $data['rating_pvp'] . " - " . $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "drop") {
	$result = array();
	foreach ($connection->query("SELECT `items`.`id`, `items`.`name` FROM `droplist` RIGHT JOIN `events` ON `events`.`boss` = `droplist`.`boss` LEFT JOIN `items` ON `items`.`id` = `droplist`.`item` WHERE `items`.`rarity` <> 'Другое' AND `events`.`id` = " . ($_GET['o'] ? $_GET['o'] : "0")) as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "bdrop") {
	$result = array();
	foreach ($connection->query("SELECT `items`.`id`, `items`.`name` FROM `droplist` LEFT JOIN `items` ON `items`.`id` = `droplist`.`item` WHERE `items`.`rarity` <> 'Другое' AND `boss` = " . ($_GET['o'] ? $_GET['o'] : "0")) as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "parties_all") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `parties` ORDER BY `name`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => "КП " . $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "parties" || $_GET['g'] == "parties_add") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `parties` WHERE `server` = {$_SESSION['user']['sid']}") as $data) {
		$result[] = array('value' => $data['id'], 'label' => "КП " . $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "servers_ds") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `servers`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "parties_ds") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `parties` WHERE `server` = " . ($_GET['o'] ? $_GET['o'] : "0")) as $data) {
		$result[] = array('value' => $data['id'], 'label' => "КП " . $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "clans_all") {
	$result = array();
	foreach ($connection->query("SELECT `clans`.`id`, CONCAT(`servers`.`code`, ': ', `clans`.`name`) AS name FROM `clans` LEFT JOIN `servers` ON `servers`.`id` = `clans`.`server` ORDER BY name") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "clans_ds") {
	if (empty($_GET['o'])) $_GET['o'] = $_SESSION['user']['sid'];

	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `clans` WHERE `server` = " . ($_GET['o'] ? $_GET['o'] : "0")) as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "classes_ds") {
	$result = array();
	foreach ($connection->query("SELECT `role_code`, `name` FROM `classes`") as $data) {
		$result[] = array('value' => $data['role_code'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "timezone") {
	$result = array();
	foreach ($connection->query("SELECT * FROM `timezone`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['label']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "items") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `items`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "uitems") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `items` WHERE `type` = 'Душа'") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "pskills") {
	$result = array();

	$array = $connection->prepare("SELECT * FROM `items` WHERE `type` = 'Скилл' AND `class` = ?");
	$array->execute(array($_SESSION['user']['class']));
	$array = $array->fetchAll(PDO::FETCH_ASSOC);

	foreach ($array as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "pitems") {
	$result = array();

	$array = $connection->prepare("SELECT * FROM `items` WHERE ((`type` = 'Оружие' AND `class` = ?) OR `type` = 'Надеваемое') AND (`rarity` = 'Героический' OR `rarity` = 'Легендарный') AND `name` NOT LIKE '%Рецепт%'");
	$array->execute(array($_SESSION['user']['class']));
	$array = $array->fetchAll(PDO::FETCH_ASSOC);

	foreach ($array as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "ritems") {
	$result = array();

	$array = $connection->prepare("SELECT * FROM `items` WHERE `rarity` = 'Редкий' AND `name` NOT LIKE '%Рецепт%'");
	$array->execute();
	$array = $array->fetchAll(PDO::FETCH_ASSOC);

	foreach ($array as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "loot") {
	$result = array();

	$limit = "HAVING `items`.`rarity` <> 'Редкий'";

	foreach ($connection->query("SELECT `loot`.`id`, `loot`.`salary`, `events`.`time`, `bosses`.`name` AS `bname`, `items`.`rarity`, `items`.`name` AS `iname`, ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) AS passed FROM `loot` JOIN `events` ON `loot`.`event` = `events`.`id` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` JOIN `items` ON `loot`.`item` = `items`.`id` WHERE `loot`.`status` = 0 AND `events`.`server` = {$_SESSION['user']['sid']} $limit ORDER BY `events`.`time` DESC") as $data) {
		if ($data['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($data['rarity'] == 'Героический') $color = 'firebrick';
		if ($data['rarity'] == 'Легендарный') $color = 'blueviolet';

		$result[] = array('value' => $data['id'], 'label' => "<span style='color: $color;'>" . date("d.m H:i", strtotime($data['time'])) . ' | ' . $data['iname'] . ' | ' . $data['bname'] . "</span>");
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "floot") {
	$result = array();

	$limit = "HAVING (passed BETWEEN 0 AND 720) OR `items`.`rarity` <> 'Редкий'";

	foreach ($connection->query("SELECT `loot`.`id`, `events`.`time`, `bosses`.`name` AS `bname`, `items`.`rarity`, `items`.`name` AS `iname`, ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) AS passed FROM `loot` JOIN `events` ON `loot`.`event` = `events`.`id` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` JOIN `items` ON `loot`.`item` = `items`.`id` WHERE `loot`.`status` = 0 AND `events`.`server` = {$_SESSION['user']['sid']} $limit ORDER BY `events`.`time` DESC") as $data) {
		if ($data['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($data['rarity'] == 'Героический') $color = 'firebrick';
		if ($data['rarity'] == 'Легендарный') $color = 'blueviolet';

		$result[] = array('value' => $data['id'], 'label' => "<span style='color: $color;'>" . date("d.m H:i", strtotime($data['time'])) . ' | ' . $data['iname'] . ' | ' . $data['bname'] . "</span>");
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "rloot") {
	$result = array();

	foreach ($connection->query("SELECT `loot`.`id`, `events`.`time`, `bosses`.`name` AS `bname`, `items`.`rarity`, `items`.`name` AS `iname` FROM `loot` JOIN `events` ON `loot`.`event` = `events`.`id` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` JOIN `items` ON `loot`.`item` = `items`.`id` WHERE `events`.`server` = {$_SESSION['user']['sid']} ORDER BY `events`.`time` DESC LIMIT 500") as $data) {
		if ($data['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($data['rarity'] == 'Героический') $color = 'firebrick';
		if ($data['rarity'] == 'Легендарный') $color = 'blueviolet';

		$result[] = array('value' => $data['id'], 'label' => "<span style='color: $color;'>" . date("d.m H:i", strtotime($data['time'])) . ' | ' . $data['iname'] . ' | ' . $data['bname'] . "</span>");
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "aloot") {
	$result = array();

	foreach ($connection->query("SELECT `loot`.`id`, `events`.`time`, `bosses`.`name` AS `bname`, `items`.`rarity`, `items`.`name` AS `iname` FROM `loot` JOIN `events` ON `loot`.`event` = `events`.`id` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` JOIN `items` ON `loot`.`item` = `items`.`id` WHERE `events`.`status` = 1 AND `events`.`server` = {$_SESSION['user']['sid']} ORDER BY `loot`.`time` DESC LIMIT 100") as $data) {
		if ($data['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($data['rarity'] == 'Героический') $color = 'firebrick';
		if ($data['rarity'] == 'Легендарный') $color = 'blueviolet';

		$result[] = array('value' => $data['id'], 'label' => "<span style='color: $color;'>" . date("d.m H:i", strtotime($data['time'])) . ' | ' . $data['iname'] . ' | ' . $data['bname'] . "</span>");
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "icons") {
	$result = array();
	foreach(glob("/var/www/html/tdkp/img/icons/loot/*.png") as $image) {
		$icon = str_replace('.png', '', str_replace('/var/www/html/tdkp/img/icons/loot/', '', $image));

		$result[] = array('value' => $icon, 'label' => $icon);
	}

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "itemrarity") {
	$result = array();
	
	$result[] = array('value' => 'Редкий', 'label' => 'Редкий');
	$result[] = array('value' => 'Героический', 'label' => 'Героический');
	$result[] = array('value' => 'Легендарный', 'label' => 'Легендарный');

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "itemtype") {
	$result = array();
	
	$result[] = array('value' => 'Надеваемое', 'label' => 'Надеваемое');
	$result[] = array('value' => 'Скилл', 'label' => 'Скилл');
	$result[] = array('value' => 'Оружие', 'label' => 'Оружие');
	$result[] = array('value' => 'Душа', 'label' => 'Душа');

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "itemtype") {
	$result = array();
	
	$result[] = array('value' => 'Надеваемое', 'label' => 'Надеваемое');
	$result[] = array('value' => 'Скилл', 'label' => 'Скилл');
	$result[] = array('value' => 'Оружие', 'label' => 'Оружие');
	$result[] = array('value' => 'Душа', 'label' => 'Душа');

	echo (json_encode($result));

	die();
}

if ($_GET['g'] == "itemclass") {
	$result = array();
	foreach ($connection->query("SELECT `id`, `name` FROM `classes`") as $data) {
		$result[] = array('value' => $data['id'], 'label' => $data['name']);
	}

	echo (json_encode($result));

	die();
}

die();
