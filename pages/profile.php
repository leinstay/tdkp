<?php

if (empty($_GET['id'])) {
	$profile = $_SESSION['user'];
} else {
	$profile = getUser($_GET['id']);

	if ($_SESSION['user']['sid'] != $profile['sid']) {
		die();
	}
}

$users_total = $connection->query("SELECT COUNT(`id`) FROM `users` WHERE `server` = {$profile['sid']}")->fetchColumn();
$user_time = $connection->query("SELECT `time` FROM `attendance` WHERE `user` = {$profile['id']} ORDER BY `attendance`.`time` ASC LIMIT 1")->fetchColumn();

$d1 = new DateTime();
$d2 = new DateTime($user_time);

$days = $d2->diff($d1)->format("%a");

if (!empty($_GET['cpid'])) {
	$profile = getCPData($_GET['cpid']);
}

$timeframe = $connection->prepare("SELECT DATE_FORMAT(`time`, '%k') + 0 AS hour, COUNT(*) AS bosses FROM `attendance` WHERE `user` = ? GROUP BY hour ORDER BY hour ASC");
$timeframe->execute(array($profile['id']));
$timeframe = $timeframe->fetchAll(PDO::FETCH_ASSOC);

$computedframe = array();
foreach ($timeframe as $time) {
	$computedframe[$time['hour']] = $time['bosses'];
}
for ($i = 0; $i < 24; $i++) {
	if ($i < 10) $t = "0" . $i . ":00";
	else $t = $i . ":00";
	$computedframe[$t] = isset($computedframe[$i]) ? $computedframe[$i] : 0;
	unset($computedframe[$i]);
}

$btimeframe = $connection->prepare("SELECT DATE_FORMAT(`next_spawn_{$profile['scode']}`, '%Y-%m-%d %H:00:00') AS hour, COUNT(*) AS bosses FROM `bosses` GROUP BY hour ORDER BY `next_spawn_{$profile['scode']}` ASC");
$btimeframe->execute();
$btimeframe = $btimeframe->fetchAll(PDO::FETCH_ASSOC);

$bcomputedframe = array();
$btcomputedframe = array();
foreach ($btimeframe as $time) {
	$bcomputedframe[$time['hour']] = $time['bosses'];
}

for ($i = 0; $i < 12; $i++) {
	$timestamp = strtotime(array_keys($bcomputedframe)[0]) + (60 * 60 * $i);
	$timestampdiff = strtotime(array_keys($bcomputedframe)[0]) + (60 * 60 * $i) + 60 * 60;
	$time = cdate('H:00', $timestamp) . ' - ' . cdate('H:00', $timestampdiff);
	$msqltime = date('Y-m-d H:i:s', $timestamp);

	$btcomputedframe[$time] = isset($bcomputedframe[$msqltime]) ? $bcomputedframe[$msqltime] : 0;
}

$party = $connection->prepare("SELECT * FROM `users` WHERE `party` = ?");
$party->execute(array($profile['party']));
$party = $party->fetchAll(PDO::FETCH_ASSOC);

$history = $connection->prepare("SELECT `comment`, `time`, `avatar`, `name` FROM `points` JOIN `users` ON `users`.`id` = `points`.`admin` WHERE `points`.`user` = ? AND `type` NOT IN ('Penalty', 'Award', 'Bonus', 'Transaction') ORDER BY `time` DESC LIMIT 50");
$history->execute(array($profile['id']));
$history = $history->fetchAll(PDO::FETCH_ASSOC);

$modhistory = $connection->prepare("SELECT `comment`, `time`, `avatar`, `name` FROM `points` JOIN `users` ON `users`.`id` = `points`.`admin` WHERE `points`.`user` = ? AND `type` IN ('Penalty', 'Award', 'Bonus') ORDER BY `time` DESC LIMIT 50");
$modhistory->execute(array($profile['id']));
$modhistory = $modhistory->fetchAll(PDO::FETCH_ASSOC);

$loothistory = $connection->prepare("SELECT `comment`, `time`, `avatar`, `name` FROM `points` JOIN `users` ON `users`.`id` = `points`.`admin` WHERE `points`.`user` = ? AND `type` IN ('Transaction') ORDER BY `time` DESC LIMIT 50");
$loothistory->execute(array($profile['id']));
$loothistory = $loothistory->fetchAll(PDO::FETCH_ASSOC);

$loot = $connection->prepare("SELECT `items`.`name`, ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) AS passed FROM `loot` JOIN `applicants` ON `loot`.`id` = `applicants`.`loot` JOIN `items` ON `loot`.`item` = `items`.`id` WHERE `loot`.`status` = 0 AND `applicants`.`user` = ? HAVING (passed BETWEEN 0 AND 720) ORDER BY `passed` ASC");
$loot->execute(array($profile['id']));
$loot = $loot->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="content">
	<div class="container-fluid p-0">
		<div class="row" id="profiler">
			<div class="col-md-4 col-xl-3">
				<div class="card" id="picturebox">
					<div class="card-header">
						<h5 class="card-title mb-0" style="float: left;">Профиль</h5>
						<h5 class="card-title mb-0" style="float: right; color: hsl(<?= ((1 - (($profile['ps'] / 10000 < 1) ? $profile['ps'] / 10000 : 1)) * 120) ?>, 75%, 85%)"><?= $profile['ps']; ?> PS</h5>
					</div>
					<div class="card-body text-center">
						<img src="<?= $profile['avatar'] ? $profile['avatar'] : 'img/adefault.png' ?>" class="img-fluid rounded-circle mb-2" width="128" height="128" />
						<h5 class="card-title mb-0"><?= $profile['name']; ?></h5>
					</div>
					<hr class="my-0" />
					<div class="card-body">
						<h5 class="h6 card-title">Персонаж</h5>
						<?php if ($profile['adm']) { ?>
							<a href="javascript:void(0)" class="badge bg-warning text-dark me-1 my-1">Сенат</a>
						<?php } ?>
						<?php if ($profile['dpl']) { ?>
							<a href="javascript:void(0)" class="badge bg-warning text-dark me-1 my-1">Дипломат</a>
						<?php } ?>
						<?php if ($profile['md']) { ?>
							<a href="javascript:void(0)" class="badge bg-warning text-dark me-1 my-1">Модератор</a>
						<?php } ?>
						<?php if ($profile['server']) { ?>
							<a href="javascript:void(0)" class="badge bg-primary me-1 my-1">Сервер: <?= $profile['sname']; ?></a>
						<?php } ?>
						<?php if ($profile['clan']) { ?>
							<a href="javascript:void(0)" class="badge bg-primary me-1 my-1">Клан: <?= $profile['kname']; ?></a>
						<?php } ?>
						<?php if ($profile['party']) { ?>
							<a href="javascript:void(0)" class="badge bg-primary me-1 my-1">КП: <?= $profile['pname']; ?></a>
						<?php } ?>
						<?php if ($profile['class']) { ?>
							<a href="javascript:void(0)" class="badge bg-primary me-1 my-1">Класс: <?= $profile['cname']; ?></a>
						<?php } ?>
						<?php if ($profile['level']) { ?>
							<a href="javascript:void(0)" class="badge bg-orange me-1 my-1">Уровень: <?= $profile['level']; ?></a>
						<?php } ?>
						<?php if ($profile['heroes_hr']) { ?>
							<a href="javascript:void(0)" class="badge bg-danger me-1 my-1">Героев H: <?= $profile['heroes_hr']; ?></a>
						<?php } ?>
						<?php if ($profile['agations_hr']) { ?>
							<a href="javascript:void(0)" class="badge bg-danger me-1 my-1">Агатионов H: <?= $profile['agations_hr']; ?></a>
						<?php } ?>
						<?php if ($profile['heroes_lg']) { ?>
							<a href="javascript:void(0)" class="badge bg-violet me-1 my-1">Героев L: <?= $profile['heroes_lg']; ?></a>
						<?php } ?>
						<?php if ($profile['agations_lg']) { ?>
							<a href="javascript:void(0)" class="badge bg-violet me-1 my-1">Агатионов L: <?= $profile['agations_lg']; ?></a>
						<?php } ?>
						<?php if ($profile['collections']) { ?>
							<a href="javascript:void(0)" class="badge bg-secondary me-1 my-1">Коллекций: <?= $profile['collections']; ?></a>
						<?php } ?>
						<?php if ($profile['rating_ps_class']) { ?>
							<a href="javascript:void(0)" class="badge bg-dark text-dark me-1 my-1">Клс. рейтинг: #<?= $profile['rating_ps_class']; ?></a>
						<?php } ?>
						<?php if ($profile['rating_ps']) { ?>
							<a href="javascript:void(0)" class="badge bg-dark text-dark me-1 my-1">Общ. рейтинг: #<?= $profile['rating_ps']; ?></a>
						<?php } ?>
						<?php if ($profile['rating_ps']) { ?>
							<a href="javascript:void(0)" class="badge bg-dark text-dark me-1 my-1">Бос. рейтинг: #<?= $profile['rating_boss']; ?></a>
						<?php } ?>
					</div>
					<hr class="my-0" />
					<div class="card-body">
						<div class="d-grid gap-2"><button onclick="show(this.id)" id="is-<?= $profile['id']; ?>" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-sm btn-danger"><span>Вещи и способности</span></button></div>
					</div>
				</div>

				<div class="card" id="wavebox">
					<script src="js/chart.min.js"></script>
					<div class="card-body">
						<h5 class="h6 card-title">Праймтайм (по МСК)</h5>
						<canvas id="prime" style="width: 100%; height: 200px;"></canvas>
						<script>
							const ctx1 = document.getElementById('prime').getContext('2d');
							const myChart1 = new Chart(ctx1, {
								type: 'bar',
								data: {
									labels: ['<?= implode("','", array_keys($computedframe)); ?>'],
									datasets: [{
										label: 'Боссов',
										data: ['<?= implode("','", $computedframe); ?>'],
										backgroundColor: [
											'rgba(255, 99, 132, 0.2)',
											'rgba(255, 99, 132, 0.2)',
											'rgba(255, 99, 132, 0.2)',
											'rgba(255, 99, 132, 0.2)',
											'rgba(54, 162, 235, 0.2)',
											'rgba(54, 162, 235, 0.2)',
											'rgba(54, 162, 235, 0.2)',
											'rgba(54, 162, 235, 0.2)',
											'rgba(255, 206, 86, 0.2)',
											'rgba(255, 206, 86, 0.2)',
											'rgba(255, 206, 86, 0.2)',
											'rgba(255, 206, 86, 0.2)',
											'rgba(75, 192, 192, 0.2)',
											'rgba(75, 192, 192, 0.2)',
											'rgba(75, 192, 192, 0.2)',
											'rgba(75, 192, 192, 0.2)',
											'rgba(153, 102, 255, 0.2)',
											'rgba(153, 102, 255, 0.2)',
											'rgba(153, 102, 255, 0.2)',
											'rgba(153, 102, 255, 0.2)',
											'rgba(255, 159, 64, 0.2)',
											'rgba(255, 159, 64, 0.2)',
											'rgba(255, 159, 64, 0.2)',
											'rgba(255, 159, 64, 0.2)',
										],
										borderColor: [
											'rgba(255, 99, 132, 1)',
											'rgba(255, 99, 132, 1)',
											'rgba(255, 99, 132, 1)',
											'rgba(255, 99, 132, 1)',
											'rgba(54, 162, 235, 1)',
											'rgba(54, 162, 235, 1)',
											'rgba(54, 162, 235, 1)',
											'rgba(54, 162, 235, 1)',
											'rgba(255, 206, 86, 1)',
											'rgba(255, 206, 86, 1)',
											'rgba(255, 206, 86, 1)',
											'rgba(255, 206, 86, 1)',
											'rgba(75, 192, 192, 1)',
											'rgba(75, 192, 192, 1)',
											'rgba(75, 192, 192, 1)',
											'rgba(75, 192, 192, 1)',
											'rgba(153, 102, 255, 1)',
											'rgba(153, 102, 255, 1)',
											'rgba(153, 102, 255, 1)',
											'rgba(153, 102, 255, 1)',
											'rgba(255, 159, 64, 1)',
											'rgba(255, 159, 64, 1)',
											'rgba(255, 159, 64, 1)',
											'rgba(255, 159, 64, 1)',
										],
										borderWidth: 1
									}]
								},
								options: {
									responsive: true,
									plugins: {
										legend: false,
									},
									scales: {
										y: {
											beginAtZero: true
										}
									}
								}
							});
						</script>
					</div>
					<hr class="my-0" />
					<div class="card-body">
						<h5 class="h6 card-title">Боссвейв</h5>
						<canvas id="bprime" style="width: 100%; height: 200px;"></canvas>
						<script>
							const ctx2 = document.getElementById('bprime').getContext('2d');
							const myChart2 = new Chart(ctx2, {
								type: 'bar',
								data: {
									labels: ['<?= implode("','", array_keys($btcomputedframe)); ?>'],
									datasets: [{
										label: 'Боссов',
										data: ['<?= implode("','", $btcomputedframe); ?>'],
										backgroundColor: [
											'rgba(255, 99, 132, 0.2)',
											'rgba(255, 99, 132, 0.2)',
											'rgba(54, 162, 235, 0.2)',
											'rgba(54, 162, 235, 0.2)',
											'rgba(255, 206, 86, 0.2)',
											'rgba(255, 206, 86, 0.2)',
											'rgba(75, 192, 192, 0.2)',
											'rgba(75, 192, 192, 0.2)',
											'rgba(153, 102, 255, 0.2)',
											'rgba(153, 102, 255, 0.2)',
											'rgba(255, 159, 64, 0.2)',
											'rgba(255, 159, 64, 0.2)',
										],
										borderColor: [
											'rgba(255, 99, 132, 1)',
											'rgba(255, 99, 132, 1)',
											'rgba(54, 162, 235, 1)',
											'rgba(54, 162, 235, 1)',
											'rgba(255, 206, 86, 1)',
											'rgba(255, 206, 86, 1)',
											'rgba(75, 192, 192, 1)',
											'rgba(75, 192, 192, 1)',
											'rgba(153, 102, 255, 1)',
											'rgba(153, 102, 255, 1)',
											'rgba(255, 159, 64, 1)',
											'rgba(255, 159, 64, 1)',
										],
										borderWidth: 1
									}]
								},
								options: {
									responsive: true,
									plugins: {
										legend: false,
									},
									scales: {
										y: {
											beginAtZero: true
										}
									}
								}
							});
						</script>
					</div>
				</div>
				<div class="card" id="constbox">
					<div class="card-body">
						<h5 class="h6 card-title">Активность</h5>
						<ul class="list-unstyled mb-0">
							<li class="mb-1" style="color: cornsilk;"><span data-feather="map-pin" class="feather-sm me-1"></span> ДКП рейтинг: Топ <?= $profile['dkp_rating'] ? $profile['dkp_rating'] : '~'; ?> <strong>[<?= $profile['dkp'] ? $profile['dkp'] : '~'; ?> pts.]</strong></li>
							<li class="mb-1"><span data-feather="map-pin" class="feather-sm me-1"></span> Боссов убито всего: <?= $profile['total_bosses'] ? $profile['total_bosses'] : '~'; ?></li>
							<li class="mb-1"><span data-feather="map-pin" class="feather-sm me-1"></span> Эпиков за неделю: <?= $profile['total_bosses_epic_last'] ? $profile['total_bosses_epic_last'] : '~'; ?></li>
							<li class="mb-1"><span data-feather="map-pin" class="feather-sm me-1"></span> Дней в альянсе: <?= $days ? $days : '~'; ?></li>
						</ul>
					</div>
					<?php if (!empty($party)) { ?>
						<hr class="my-0" />
						<div class="card-body">
							<h5 class="h6 card-title">Состав КП</h5>
							<ul class="list-unstyled mb-0">
								<?php foreach ($party as $user) {
									$user['name'] = '<a target="_blank" rel="noopener noreferrer" href="/?p=profile&id=' . $user['id'] . '">' . $user['name'] . '</a>'; ?>
									<li class="mb-1"><span data-feather="user" class="feather-sm me-1"></span> <?= "<strong>{$user['name']}</strong>"; ?></span></li>
								<?php } ?>
							</ul>
						</div>
					<?php } ?>
					<?php if (!empty($loot)) { ?>
						<hr class="my-0" />
						<div class="card-body">
							<h5 class="h6 card-title">Претендуют в данный момент</h5>
							<ul class="list-unstyled mb-0">
								<?php foreach ($loot as $item) { ?>
									<li class="mb-1"><span data-feather="codesandbox" class="feather-sm me-1"></span> <?= $item['name']; ?></span></li>
								<?php } ?>
							</ul>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="col-md-8 col-xl-9">
				<div class="card">
					<div class="card-header">
						<h5 class="card-title mb-0">События</h5>
					</div>
					<div class="card-body" id="history" style="overflow: auto; margin-bottom: 20px; height: 68vh;">
						<?php
						if (!empty($history)) {
							foreach ($history as $entry) {
								$data = $templates['events'];

								$data = str_replace('{{link}}', $entry['avatar'] ? $entry['avatar'] : 'img/adefault.png', $data);
								$data = str_replace('{{time}}', cdate("Y-m-d H:i:s", strtotime($entry['time'])), $data);
								$data = str_replace('{{comment}}', $entry['comment'], $data);
								$data = str_replace('{{user}}', $entry['name'], $data);
								$data = str_replace('{{ago}}', timeElapsed(date("Y-m-d H:i:s", strtotime($entry['time']))), $data);

								echo $data;
							}
						} else {
							$data = $templates['events'];

							$data = str_replace('{{link}}', 'img/adefault.png', $data);
							$data = str_replace('{{time}}', cdate("Y-m-d H:i:s"), $data);
							$data = str_replace('{{user}}', 'Админ', $data);
							$data = str_replace('{{comment}}', 'Вы еще не принимали участие ни в одном событии.', $data);
							$data = str_replace('{{ago}}', timeElapsed(date("Y-m-d H:i:s")), $data);

							echo $data;
						}
						?>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h5 class="card-title mb-0">Распределение</h5>
					</div>
					<div class="card-body" id="loothistory" style="overflow: auto; margin-bottom: 20px; height: 68vh;">
						<?php
						if (!empty($loothistory)) {
							foreach ($loothistory as $entry) {
								$data = $templates['events'];

								$data = str_replace('{{link}}', $entry['avatar'] ? $entry['avatar'] : 'img/adefault.png', $data);
								$data = str_replace('{{time}}', cdate("Y-m-d H:i:s", strtotime($entry['time'])), $data);
								$data = str_replace('{{comment}}', $entry['comment'], $data);
								$data = str_replace('{{user}}', $entry['name'], $data);
								$data = str_replace('{{ago}}', timeElapsed(date("Y-m-d H:i:s", strtotime($entry['time']))), $data);

								echo $data;
							}
						} else {
							$data = $templates['events'];

							$data = str_replace('{{link}}', 'img/adefault.png', $data);
							$data = str_replace('{{time}}', cdate("Y-m-d H:i:s"), $data);
							$data = str_replace('{{user}}', 'Админ', $data);
							$data = str_replace('{{comment}}', 'Вы еще не получили ни единого предмета.', $data);
							$data = str_replace('{{ago}}', timeElapsed(date("Y-m-d H:i:s")), $data);

							echo $data;
						}
						?>
					</div>
				</div>
				<div class="card">
					<div class="card-header">
						<h5 class="card-title mb-0">Модерация</h5>
					</div>
					<div class="card-body" id="modhistory" style="overflow: auto; margin-bottom: 20px; height: 68vh;">
						<?php
						if (!empty($modhistory)) {
							foreach ($modhistory as $entry) {
								$data = $templates['events'];

								$data = str_replace('{{link}}', $entry['avatar'] ? $entry['avatar'] : 'img/adefault.png', $data);
								$data = str_replace('{{time}}', cdate("Y-m-d H:i:s", strtotime($entry['time'])), $data);
								$data = str_replace('{{comment}}', $entry['comment'], $data);
								$data = str_replace('{{user}}', $entry['name'], $data);
								$data = str_replace('{{ago}}', timeElapsed(date("Y-m-d H:i:s", strtotime($entry['time']))), $data);

								echo $data;
							}
						} else {
							$data = $templates['events'];

							$data = str_replace('{{link}}', 'img/adefault.png', $data);
							$data = str_replace('{{time}}', cdate("Y-m-d H:i:s"), $data);
							$data = str_replace('{{user}}', 'Админ', $data);
							$data = str_replace('{{comment}}', 'Вы еще не получали штрафы и поощрения.', $data);
							$data = str_replace('{{ago}}', timeElapsed(date("Y-m-d H:i:s")), $data);

							echo $data;
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>