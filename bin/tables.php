<?php
include_once("config.php");

$table = array("data" => array());

if (empty($_SESSION['user']['id']))
	die(json_encode($table));

if ($_GET['action'] == "all-rare-loot") {
	$query = "SELECT `loot`.*, `items`.`name` AS iname, `items`.`rarity`, `items`.`icon`, `user_from`.`name` AS fname, 
	`user_from`.`id` AS fid, `user`.`id` AS tid, `user_admin`.`id` AS aid, `user`.`name` AS tname, 
	`bosses`.`name` AS bname, `user_admin`.`name` AS aname, `applicants`.`id` AS appid
		FROM `loot` 
		LEFT JOIN `applicants` ON `loot`.`id` = `applicants`.`loot` AND `applicants`.`user` = ?
		LEFT JOIN `users` user_admin ON `loot`.`admin` = user_admin.`id`
		LEFT JOIN `clans` user_from ON `loot`.`clan` = user_from.`id`
		LEFT JOIN `users` user ON `loot`.`user` = user.`id`
		LEFT JOIN `items` ON `loot`.`item` = `items`.`id`
		LEFT JOIN `events` ON `loot`.`event` = `events`.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE (`items`.`rarity` = 'Редкий') AND `loot`.`status` = 0 AND `events`.`server` = ? ORDER BY `loot`.`time` DESC";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['id'], $_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$row['from'] = $row['fname'];

		$row['aname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['aid'] . '">' . $row['aname'] . '</a>';
		$row['tname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['tid'] . '">' . $row['tname'] . '</a>';

		$edit = '';
		if (strtotime($row['time']) < time() - 60 * 60 * 60) {
			continue;
		} else if (strtotime($row['time']) < time() - 60 * 60 * 36) {
			$row['iname'] = "<strike>{$row['iname']}</span>";
		} else if (strtotime($row['time']) < time() - 60 * 60 * 12 && $row['salary']) {
			$row['iname'] .= " <span style='color:white'>(*)</span>";
		} else {
			$edit .= '<a href="javascript:void(0);" onclick="show(this.id)" id="ll-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Список претендентов</a>';

			if ($row['appid'])
				$edit .= '<br><span><a target="_blank" rel="noopener noreferrer" href="/?l=' . md5($row['id']) . '&a=remove">Отказаться</a>';
			else
				$edit .= '<br><span><a target="_blank" rel="noopener noreferrer" href="/?l=' . md5($row['id']) . '">Претендовать</a></span>';
		}

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), "<img style='height: 50px;' src='/img/icons/loot/" . $row['icon'] . ".png'>", "<strong style='color: $color;'>" . $row['iname'] . "</strong>", $row['discount'] ? "10%" : "20%", $row['bname'], $row['fname'], $edit);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "all-epic-loot") {
	$query = "SELECT `loot`.*, `items`.`name` AS iname, `items`.`rarity`, `items`.`icon`, `user_from`.`name` AS fname, 
	`user_from`.`id` AS fid, `user`.`id` AS tid, `user_admin`.`id` AS aid, `user`.`name` AS tname, 
	`bosses`.`name` AS bname, `user_admin`.`name` AS aname
		FROM `loot` 
		LEFT JOIN `users` user_admin ON `loot`.`admin` = user_admin.`id`
		LEFT JOIN `clans` user_from ON `loot`.`clan` = user_from.`id`
		LEFT JOIN `users` user ON `loot`.`user` = user.`id`
		LEFT JOIN `items` ON `loot`.`item` = `items`.`id`
		LEFT JOIN `events` ON `loot`.`event` = `events`.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE (`items`.`rarity` = 'Героический' OR `items`.`rarity` = 'Легендарный') AND `loot`.`status` = 0 AND `events`.`server` = ? ORDER BY `loot`.`time` DESC LIMIT 100";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$row['from'] = $row['fname'];

		$row['aname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['aid'] . '">' . $row['aname'] . '</a>';
		$row['tname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['tid'] . '">' . $row['tname'] . '</a>';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), "<img style='height: 50px;' src='/img/icons/loot/" . $row['icon'] . ".png'>", "<strong style='color: $color;'>" . $row['iname'] . "</strong>", $row['bname'], $row['fname']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "rare-loot") {
	$query = "SELECT `loot`.*, `items`.`name` AS iname, `items`.`rarity`, `items`.`icon`, `user_from`.`name` AS fname, 
	`user_from`.`id` AS fid, `user`.`id` AS tid, `user_admin`.`id` AS aid, `user_checked`.`id` AS chid, `user`.`name` AS tname, 
	`bosses`.`name` AS bname, `user_admin`.`name` AS aname, `user_checked`.`name` AS chname, `events`.`close`
		FROM `loot` 
		LEFT JOIN `users` user_checked ON `loot`.`checked_id` = user_checked.`id`
		LEFT JOIN `users` user_admin ON `loot`.`admin` = user_admin.`id`
		LEFT JOIN `clans` user_from ON `loot`.`clan` = user_from.`id`
		LEFT JOIN `users` user ON `loot`.`user` = user.`id`
		LEFT JOIN `items` ON `loot`.`item` = `items`.`id`
		LEFT JOIN `events` ON `loot`.`event` = `events`.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE `items`.`rarity` = 'Редкий' AND `loot`.`status` = 1 AND `events`.`server` = ? ORDER BY `loot`.`time` DESC LIMIT 100";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$row['tname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['tid'] . '">' . $row['tname'] . '</a>';
		$row['chname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['chid'] . '">' . $row['chname'] . '</a>';

		if (!$row['received']) $check = "";
		else $check = "checked";

		$checkbox = '<input class="form-check-input" disabled ' . $check . ' type="checkbox" id="cl-' . $row["id"] . '">';

		$options = "";

		if (!$row['checked']) $check = "";
		else $check = "checked";

		if (($_SESSION['user']['md'] || $_SESSION['user']['adm']) && !$row['checked'] && $row['received']) $disabled = "";
		else $disabled = "disabled";

		$checkboxch = '<input class="form-check-input lootcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="lc-' . $row["id"] . '">';

		$options .= '<a href="javascript:void(0);" onclick="show(this.id)" id="im-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Посмотреть</a>';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), cdate("d.m H:i", strtotime($row['close'])), "<img style='height: 50px;' src='/img/icons/loot/" . $row['icon'] . ".png'>", "<strong style='color: $color;'>" . $row['iname'] . "</strong>", $row['bname'], $row['fname'], $row['tname'], $row['rating'] ? "#" . $row['rating'] : "", $row['points'] != null ? $row['points'] . " pts." : "~");
	}

	die(json_encode($table));
}

if ($_GET['action'] == "rare-loot-mod") {
	$query = "SELECT `loot`.*, `items`.`name` AS iname, `items`.`rarity`, `items`.`icon`, `user_from`.`name` AS fname, 
	`user_from`.`id` AS fid, `user`.`id` AS tid, `user_admin`.`id` AS aid, `user_checked`.`id` AS chid, `user`.`name` AS tname, 
	`bosses`.`name` AS bname, `user_admin`.`name` AS aname, `user_checked`.`name` AS chname, `events`.`close`
		FROM `loot` 
		LEFT JOIN `users` user_checked ON `loot`.`checked_id` = user_checked.`id`
		LEFT JOIN `users` user_admin ON `loot`.`admin` = user_admin.`id`
		LEFT JOIN `clans` user_from ON `loot`.`clan` = user_from.`id`
		LEFT JOIN `users` user ON `loot`.`user` = user.`id`
		LEFT JOIN `items` ON `loot`.`item` = `items`.`id`
		LEFT JOIN `events` ON `loot`.`event` = `events`.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE `items`.`rarity` = 'Редкий' AND `loot`.`status` = 1 AND `events`.`server` = ? ORDER BY `time` DESC LIMIT 200";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$row['tname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['tid'] . '">' . $row['tname'] . '</a>';
		$row['chname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['chid'] . '">' . $row['chname'] . '</a>';

		if (!$row['received']) $check = "";
		else $check = "checked";

		$checkbox = '<input class="form-check-input rescheck" ' . $check . ' type="checkbox" id="cl-' . $row["id"] . '">';

		$options = "";

		if (!$row['checked']) $check = "";
		else $check = "checked";

		if (($_SESSION['user']['md'] || $_SESSION['user']['adm']) && !$row['checked']) $disabled = "";
		else $disabled = "disabled";

		$checkboxch = '<input class="form-check-input lootcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="lc-' . $row["id"] . '">';

		$options .= '<a href="javascript:void(0);" onclick="show(this.id)" id="im-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Посмотреть</a>';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), cdate("d.m H:i", strtotime($row['close'])), "<img style='height: 50px;' src='/img/icons/loot/" . $row['icon'] . ".png'>", "<strong style='color: $color;'>" . $row['iname'] . "</strong>", $row['bname'], $row['fname'], $row['tname'], $checkboxch);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "epic-loot") {
	$query = "SELECT `loot`.*, `items`.`name` AS iname, `items`.`rarity`, `items`.`icon`, `user_from`.`name` AS fname, 
	`user_from`.`id` AS fid, `user`.`id` AS tid, `user_admin`.`id` AS aid, `user`.`name` AS tname, 
	`bosses`.`name` AS bname, `user_admin`.`name` AS aname, `events`.`close`
		FROM `loot` 
		LEFT JOIN `users` user_admin ON `loot`.`admin` = user_admin.`id`
		LEFT JOIN `clans` user_from ON `loot`.`clan` = user_from.`id`
		LEFT JOIN `users` user ON `loot`.`user` = user.`id`
		LEFT JOIN `items` ON `loot`.`item` = `items`.`id`
		LEFT JOIN `events` ON `loot`.`event` = `events`.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE (`items`.`rarity` = 'Героический' OR `items`.`rarity` = 'Легендарный') AND `loot`.`status` = 1 AND `events`.`server` = ? LIMIT 100";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$row['from'] = $row['fname'];

		$row['aname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['aid'] . '">' . $row['aname'] . '</a>';
		$row['tname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['tid'] . '">' . $row['tname'] . '</a>';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), cdate("d.m H:i", strtotime($row['close'])), "<img style='height: 50px;' src='/img/icons/loot/" . $row['icon'] . ".png'>", "<strong style='color: $color;'>" . $row['iname'] . "</strong>", $row['bname'], $row['fname'], $row['aname'], $row['tname']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "my-loot") {
	$query = "SELECT `loot`.*, `items`.`name` AS iname, `items`.`rarity`, `items`.`icon`, `user_from`.`name` AS fname, 
	`user_from`.`id` AS fid, `user`.`id` AS tid, `user_admin`.`id` AS aid, `user`.`name` AS tname, 
	`bosses`.`name` AS bname, `user_admin`.`name` AS aname, `events`.`close`
		FROM `loot` 
		LEFT JOIN `users` user_admin ON `loot`.`admin` = user_admin.`id`
		LEFT JOIN `clans` user_from ON `loot`.`clan` = user_from.`id`
		LEFT JOIN `users` user ON `loot`.`user` = user.`id`
		LEFT JOIN `items` ON `loot`.`item` = `items`.`id`
		LEFT JOIN `events` ON `loot`.`event` = `events`.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		WHERE `items`.`rarity` = 'Редкий' AND `loot`.`status` = 1 AND `events`.`server` = ? AND `loot`.`user` = ? ORDER BY `time` DESC ";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['sid'], $_SESSION['user']['id']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		if ($row['rarity'] == 'Редкий') $color = 'cornflowerblue';
		if ($row['rarity'] == 'Героический') $color = 'firebrick';
		if ($row['rarity'] == 'Легендарный') $color = 'blueviolet';

		$row['tname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['tid'] . '">' . $row['tname'] . '</a>';

		if (!$row['received']) $check = "";
		else $check = "checked";

		$checkbox = '<input class="form-check-input" disabled ' . $check . ' type="checkbox" id="cl-' . $row["id"] . '">';

		$options = '<a href="javascript:void(0);" onclick="show(this.id)" id="im-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Посмотреть</a>';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), cdate("d.m H:i", strtotime($row['close'])), "<img style='height: 50px;' src='/img/icons/loot/" . $row['icon'] . ".png'>", "<strong style='color: $color;'>" . $row['iname'] . "</strong>", $row['bname'], $row['fname'], $row['tname'], $row['rating'] ? "#" . $row['rating'] : "", $row['points'] != null ? $row['points'] . " pts." : "~");
	}

	die(json_encode($table));
}

if ($_GET['action'] == "boss-events") {
	$query = "SELECT * FROM `bosses`";

	$data = $connection->prepare($query);
	$data->execute();

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$show = '<a href="javascript:void(0);" onclick="show(this.id)" id="dl-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Показать</a>';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['last_spawn_' . $_SESSION['user']['scode']])), cdate("d.m H:i", strtotime($row['next_spawn_' . $_SESSION['user']['scode']])), "<img style='height: 40px;' src='/img/icons/boss/" . $row['icon'] . ".png'>", ($row['dkp'] >= 35) ? "<strong style='color: firebrick;'>" . $row['name'] . "</strong>" : $row['name'], $row['respawn'] . " час(а/ов)", $row['chance'] . "%", $row['dkp'] . " pts.", $show);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "all-events") {
	$query = "SELECT `events`.*, `bosses`.`name` AS bname, `bosses`.`icon`, `bosses`.`dkp`, `user_checked`.`id` AS chid, `user_checked`.`name` AS chname, `users`.`name` AS uname, `users`.`id` AS uid, 
	(SELECT COUNT(*) FROM `attendance` WHERE `event` = `events`.`id`) AS cnt, 
	(SELECT COUNT(*) FROM `attendance` WHERE `event` = `events`.`id` AND `user` = ?) AS atd
		FROM `events`
		LEFT JOIN `users` user_checked ON `events`.`checked_id` = user_checked.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		LEFT JOIN `users` ON `events`.`admin` = `users`.`id`
		WHERE `events`.`server` = ?
		ORDER BY `time` DESC LIMIT 100";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['id'], $_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$row['uname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['uid'] . '">' . $row['uname'] . '</a>';
		$row['chname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['chid'] . '">' . $row['chname'] . '</a>';

		$show = '<a href="javascript:void(0);" onclick="show(this.id)" id="ua-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Показать</a> (' . $row['cnt'] . ')';

		if (empty($row['atd'])) $options = '<a onclick="attend(this.id)" id="ae-' . $row["id"] . '-add" href="javascript:void(0);">Участвовал</a>';
		else $options = '<a onclick="attend(this.id)" id="ae-' . $row["id"] . '-remove" href="javascript:void(0);">Не участвовал</a>';

		if ($row['status']) $options = "";

		if (($_SESSION['user']['md'] || $_SESSION['user']['adm']) && !$row['status']) $disabled = "";
		else $disabled = "disabled";

		if (!$row['pvp']) $check = "";
		else $check = "checked";

		$checkbox = '<input class="form-check-input pvpcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="pp-' . $row["id"] . '">';

		if (!$row['checked']) $check = "";
		else $check = "checked";

		if (($_SESSION['user']['md'] || $_SESSION['user']['adm']) && !$row['checked'] && $row['status']) $disabled = "";
		else $disabled = "disabled";

		$checkboxch = '<input class="form-check-input evntcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="ec-' . $row["id"] . '">';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), $row['close'] ? cdate("d.m H:i", strtotime($row['close'])) : "Отсутствует", "<img style='height: 40px;' src='/img/icons/boss/" . $row['icon'] . ".png'>", ($row['dkp'] >= 35) ? "<strong style='color: firebrick;'>" . $row['bname'] . "</strong>" : $row['bname'], $row['uname'], $row['status'] ? "Закрыто" : "Открыто", $show, $options);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "all-events-mod") {
	$query = "SELECT `events`.*, `bosses`.`name` AS bname, `bosses`.`icon`, `user_checked`.`id` AS chid, `user_checked`.`name` AS chname, `users`.`name` AS uname, `users`.`id` AS uid, 
	(SELECT COUNT(*) FROM `attendance` WHERE `event` = `events`.`id`) AS cnt, 
	(SELECT COUNT(*) FROM `attendance` WHERE `event` = `events`.`id` AND `user` = ?) AS atd
		FROM `events`
		LEFT JOIN `users` user_checked ON `events`.`checked_id` = user_checked.`id`
		LEFT JOIN `bosses` ON `events`.`boss` = `bosses`.`id`
		LEFT JOIN `users` ON `events`.`admin` = `users`.`id`
		WHERE `events`.`server` = ?
		ORDER BY `time` DESC LIMIT 100";

	$data = $connection->prepare($query);
	$data->execute(array($_SESSION['user']['id'], $_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$row['uname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['uid'] . '">' . $row['uname'] . '</a>';
		$row['chname'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $row['chid'] . '">' . $row['chname'] . '</a>';

		$show = '<a href="javascript:void(0);" onclick="show(this.id)" id="ua-' . $row["id"] . '" data-bs-toggle="modal" data-bs-target="#userModal">Показать</a> (' . $row['cnt'] . ')';

		if (empty($row['atd'])) $options = '<a href="/?p=events&c=' . md5($row["id"]) . '">Участвовал</a>';
		else $options = '<a href="/?p=events&c=' . md5($row["id"]) . '&a=remove">Не участвовал</a>';

		if ($row['status']) $options = "";

		if (($_SESSION['user']['md'] || $_SESSION['user']['adm']) && !$row['status']) $disabled = "";
		else $disabled = "disabled";

		if (!$row['pvp']) $check = "";
		else $check = "checked";

		$checkbox = '<input class="form-check-input pvpcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="pp-' . $row["id"] . '">';

		if (!$row['checked']) $check = "";
		else $check = "checked";

		if (($_SESSION['user']['md'] || $_SESSION['user']['adm']) && !$row['checked'] && $row['status']) $disabled = "";
		else $disabled = "disabled";

		$checkboxch = '<input class="form-check-input evntcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="ec-' . $row["id"] . '">';

		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), $row['close'] ? cdate("d.m H:i", strtotime($row['close'])) : "Отсутствует", "<img style='height: 40px;' src='/img/icons/boss/" . $row['icon'] . ".png'>", $row['bname'], $row['uname'], $row['status'] ? "Закрыто" : "Открыто", $show, $checkboxch, $checkbox);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "dkp-ratings") {
	$data = $connection->prepare("SELECT `users`.* FROM `users` WHERE `users`.`server` = ? AND `dkp` > 0");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['dkp_rating'], '<span ' . $astyle . '>' . $row['name'] . '</span>', $row['dkp'], ceil($row['dkp'] * 0.8), ceil($row['dkp'] * 0.9));
	}

	die(json_encode($table));
}

if ($_GET['action'] == "bonus-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `clans`.`name` AS cname FROM `users` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` WHERE `users`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['rating_bonus'], $row['name'], $row['cname'], $row['points_bonus']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "cardsconst-ratings") {
	$data = $connection->prepare("SELECT * FROM `parties` WHERE `parties`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['party']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&cpid=' . $row['id'] . '">' . "КП " . $row['name'] . '</a>';

		$table['data'][] = array($row['rating_cards'], '<span ' . $astyle . '>' . $row['name'] . '</span>', $row['points_cards']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "colsconst-ratings") {
	$data = $connection->prepare("SELECT * FROM `parties` WHERE `parties`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['party']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&cpid=' . $row['id'] . '">' . "КП " . $row['name'] . '</a>';

		$table['data'][] = array($row['rating_collections'], '<span ' . $astyle . '>' . $row['name'] . '</span>', $row['points_collections']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "cards-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `clans`.`name` AS cname FROM `users` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` WHERE `users`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['rating_cards'], $row['name'], $row['cname'], $row['points_cards']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "ps-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `clans`.`name` AS cname , `classes`.`name` AS clname FROM `users` LEFT JOIN `classes` ON `users`.`class` = `classes`.`id` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` WHERE `users`.`server` = ? AND `users`.`ps` > 1000");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['rating_ps'], $row['rating_ps_class'], $row['name'], $row['clname'], $row['cname'], $row['ps']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "cols-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `clans`.`name` AS cname FROM `users` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` WHERE `users`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['rating_collections'], $row['name'], $row['cname'], $row['collections']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "boss-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `parties`.`name` AS pname, `clans`.`name` AS cname, `classes`.`name` AS clname FROM `users` LEFT JOIN `parties` ON `users`.`party` = `parties`.`id` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` LEFT JOIN `classes` ON `users`.`class` = `classes`.`id` WHERE `users`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['name'], $row['cname'], $row['clname'], $row['pname'] ? "КП " . $row['pname'] : "~", $row['total_bosses'], $row['total_bosses_last'], $row['total_bosses_epic'], $row['total_bosses_epic_last'], $row['total_items'], $row['total_items_last']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "admin-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `servers`.`name` AS sname, `parties`.`name` AS pname, `clans`.`name` AS cname, `classes`.`name` AS clname FROM `users` LEFT JOIN `classes` ON `users`.`class` = `classes`.`id` LEFT JOIN `servers` ON `users`.`server` = `servers`.`id` LEFT JOIN `parties` ON `users`.`party` = `parties`.`id` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` WHERE `users`.`clan` IS NOT NULL AND `users`.`total_bosses_epic_last` > 0");
	$data->execute();

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$row['heroes_all'] = $row['heroes_all'] . " (<span style='color: red;'>{$row['heroes_hr']}</span>/" . "<span style='color: violet;'>{$row['heroes_lg']}</span>)";
		$row['agations_all'] = $row['agations_all'] . " (<span style='color: red;'>{$row['agations_hr']}</span>/" . "<span style='color: violet;'>{$row['agations_lg']}</span>)";

		$table['data'][] = array("<img style='height: 32px; border-radius: 16px;' src='" . ($row['avatar'] ? str_replace('?size=2048', '?size=32', $row['avatar']) : "img/adefault.png") . "'>", $row['name'], $row['clname'], $row['sname'], $row['cname'], $row['pname'] ? "КП " . $row['pname'] : "~", $row['ps'], $row['level'], $row['prime'] . " час(а/ов)", $row['defence'], $row['reduction'], $row['resistance'], $row['seal'], $row['heroes_all'], $row['agations_all'], $row['collections'], $row['ready'] ? "Готов" : "Не готов", $row['total_bosses_last'], $row['total_bosses_epic_last']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "mod-ratings") {
	$data = $connection->prepare("SELECT `users`.*, `servers`.`name` AS sname, `parties`.`name` AS pname, `clans`.`name` AS cname, `classes`.`name` AS clname FROM `users` LEFT JOIN `classes` ON `users`.`class` = `classes`.`id` LEFT JOIN `servers` ON `users`.`server` = `servers`.`id` LEFT JOIN `parties` ON `users`.`party` = `parties`.`id` LEFT JOIN `clans` ON `users`.`clan` = `clans`.`id` WHERE `users`.`clan` IS NOT NULL AND `users`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$row['heroes_all'] = $row['heroes_all'] . " (<span style='color: red;'>{$row['heroes_hr']}</span>/" . "<span style='color: violet;'>{$row['heroes_lg']}</span>)";
		$row['agations_all'] = $row['agations_all'] . " (<span style='color: red;'>{$row['agations_hr']}</span>/" . "<span style='color: violet;'>{$row['agations_lg']}</span>)";

		$table['data'][] = array("<img style='height: 32px; border-radius: 16px;' src='" . ($row['avatar'] ? str_replace('?size=2048', '?size=32', $row['avatar']) : "img/adefault.png") . "'>", $row['name'], $row['clname'], $row['sname'], $row['cname'], $row['pname'] ? "КП " . $row['pname'] : "~", $row['ps'], $row['level'], $row['prime'] . " час(а/ов)", $row['defence'], $row['reduction'], $row['resistance'], $row['seal'], $row['heroes_all'], $row['agations_all'], $row['collections'], $row['ready'] ? "Готов" : "Не готов", $row['total_bosses_last'], $row['total_bosses_epic_last']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "bossconst-ratings") {
	$data = $connection->prepare("SELECT * FROM `parties` WHERE `parties`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['party']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&cpid=' . $row['id'] . '">' . "КП " . $row['name'] . '</a>';

		$table['data'][] = array('<span ' . $astyle . '>' . $row['name'] . '</span>', $row['total_bosses'], $row['total_bosses_last'], $row['total_bosses_epic'], $row['total_bosses_epic_last'], $row['total_items'], $row['total_items_last']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "bossclan-ratings") {
	$data = $connection->prepare("SELECT * FROM `clans` WHERE `clans`.`server` = ?");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['clan']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$table['data'][] = array('<span ' . $astyle . '>' . $row['name'] . '</span>', $row['total_bosses'], $row['total_bosses_last'], $row['total_bosses_epic'], $row['total_bosses_epic_last'], $row['total_items'], $row['total_items_last']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "points-history") {
	$data = $connection->prepare("SELECT `points`.*, a.name AS uname, b.name AS aname
		FROM `points`
		LEFT JOIN users a ON `points`.`user` = a.id
		LEFT JOIN users b ON `points`.`admin` = b.id
		WHERE `type` = 'Penalty' OR `type` = 'Award'  
		ORDER BY `points`.`time` DESC LIMIT 200");
	$data->execute();

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), $row['uname'], $row['aname'], $row['dkp'], $row['comment']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "pvp-ratings") {
	$data = $connection->prepare("SELECT `classes`.`name` as class, winner.`name` as wname,loser.`name` as lname, `winner_rating`, `loser_rating`, `time` 
		FROM `pvp`
		LEFT JOIN `users` winner ON `pvp`.`winner` = winner.`id`
		LEFT JOIN `users` loser ON `pvp`.`loser` = loser.`id`
		LEFT JOIN `classes` ON `pvp`.`class` = `classes`.`id`
		WHERE `winner`.`server` = ? 
		ORDER BY `time`");
	$data->execute(array($_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$table['data'][] = array(cdate("d.m H:i", strtotime($row['time'])), $row['class'], $row['wname'], $row['winner_rating'], $row['lname'], $row['loser_rating']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "knifes-ratings" || $_GET['action'] == "mages-ratings" || $_GET['action'] == "archers-ratings" || $_GET['action'] == "orbs-ratings" || $_GET['action'] == "tanks-ratings" || $_GET['action'] == "gladiators-ratings" || $_GET['action'] == "warlords-ratings") {
	switch ($_GET['action']) {
		case "knifes-ratings":
			$class = 1;
			break;
		case "mages-ratings":
			$class = 2;
			break;
		case "archers-ratings":
			$class = 3;
			break;
		case "orbs-ratings":
			$class = 4;
			break;
		case "tanks-ratings":
			$class = 5;
			break;
		case "gladiators-ratings":
			$class = 6;
			break;
		case "warlords-ratings":
			$class = 7;
			break;
	}

	$data = $connection->prepare("SELECT `id`, `rating_pvp` AS `rating`, `name` FROM `users` 
	WHERE `class` = ? AND `rating_pvp` <> 99 AND `users`.`server` = ?
	ORDER BY `rating_pvp`");
	$data->execute(array($class, $_SESSION['user']['sid']));

	while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
		$astyle = ($row['id'] == $_SESSION['user']['id']) ? "style='font-weight: bold; color: blanchedalmond;'" : "";
		$row['name'] = '<a target="_blank" ' . $astyle . ' rel="noopener noreferrer" href="/?p=profile&id=' . $row['id'] . '">' . $row['name'] . '</a>';

		$table['data'][] = array($row['rating'], $row['name']);
	}

	die(json_encode($table));
}

if ($_GET['action'] == "all-bl") {
	if ($_GET['i']) {
		$holdings = $connection->prepare("SELECT `user` FROM `souls` WHERE `soul` = ?");
		$holdings->execute(array($_GET['i']));
		$holdings = $holdings->fetchAll(PDO::FETCH_COLUMN, 0);

		$item = $connection->prepare("SELECT * FROM `items` WHERE `id` = ?");
		$item->execute(array($_GET['i']));
		$item = $item->fetch(PDO::FETCH_ASSOC);

		if ($_GET['clan']) {
			$limit = "AND `clan` = " . $_GET['clan'];
		}

		$users = $connection->prepare("SELECT `id`, `name`, `ps`, `ready`, `total_bosses_epic_last`, (SELECT COUNT(*) FROM `souls` WHERE `souls`.`user` = `users`.`id`) AS total FROM `users` WHERE `users`.`server` = ? $limit AND `ps` > 1000 ORDER BY `total_bosses_epic_last` DESC");
		$users->execute(array($_SESSION['user']['sid']));

		while ($row = $users->fetch(PDO::FETCH_ASSOC)) {
			if ($item['rarity'] == 'Редкий') $color = 'cornflowerblue';
			if ($item['rarity'] == 'Героический') $color = 'firebrick';
			if ($item['rarity'] == 'Легендарный') $color = 'blueviolet';

			if ($row['ready'])
				$rd = "Лечу";
			else
				$rd = "Остаюсь";

			if (in_array($row['id'], $holdings)) {
				$hold = "Имеется";
				$check = "checked";
			} else {
				$hold = "Отсутствует";
				$check = "";
			}

			if (($_SESSION['user']['md'] || $_SESSION['user']['adm'] || $row['id'] == $_SESSION['user']['id']) && !in_array($row['id'], $holdings)) $disabled = "";
			else $disabled = "disabled";

			$checkbox = '<input class="form-check-input soulcheck" ' . $disabled . ' ' . $check . ' type="checkbox" id="so-' . $row["id"] . "-" . $item['id'] . '">';

			$table['data'][] = array("<strong style='color: $color;'>" . $item['name'] . "</strong>", $row['name'], $row['total_bosses_epic_last'], $row['ps'], $row['total'], $rd, $hold, $checkbox);
		}
	}

	die(json_encode($table));
}

die(json_encode($table));
