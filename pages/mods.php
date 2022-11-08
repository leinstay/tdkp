<?php
$query = "SELECT 
	(SELECT CEIL(AVG(dd.defence)) as defence
	FROM (
	SELECT d.defence, @rownum1:=@rownum1+1 as `row_number`, @total_rows1:=@rownum1
	FROM users d, (SELECT @rownum1:=0) r
	WHERE d.defence IS NOT NULL AND d.ps <> 1000 AND d.server = {$_SESSION['user']['sid']}
	ORDER BY d.defence
	) as dd
	WHERE dd.row_number IN (FLOOR((@total_rows1+1)/2), FLOOR((@total_rows1+2)/2))) AS defence,
	(SELECT CEIL(AVG(dd.reduction)) as reduction
	FROM (
	SELECT d.reduction, @rownum2:=@rownum2+1 as `row_number`, @total_rows2:=@rownum2
	FROM users d, (SELECT @rownum2:=0) r
	WHERE d.reduction IS NOT NULL AND d.ps <> 1000 AND d.server = {$_SESSION['user']['sid']}
	ORDER BY d.reduction
	) as dd
	WHERE dd.row_number IN (FLOOR((@total_rows2+1)/2), FLOOR((@total_rows2+2)/2))) AS reduction,
	(SELECT CEIL(AVG(dd.resistance)) as resistance
	FROM (
	SELECT d.resistance, @rownum3:=@rownum3+1 as `row_number`, @total_rows3:=@rownum3
	FROM users d, (SELECT @rownum3:=0) r
	WHERE d.resistance IS NOT NULL AND d.ps <> 1000 AND d.server = {$_SESSION['user']['sid']}
	ORDER BY d.resistance
	) as dd
	WHERE dd.row_number IN (FLOOR((@total_rows3+1)/2), FLOOR((@total_rows3+2)/2))) AS resistance";

$data = $connection->prepare($query);
$data->execute();
$data = $data->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($_SESSION['user']['adm'] || $_SESSION['user']['md'] || $_SESSION['user']['id'] == 8) { ?>
	<main class="content">
		<div class="container-fluid p-0">
			<div class="row">
				<div class="col-md-12 col-xl-12">
					<div class="card mb-3">
						<div class="card-header">
							<h5 class="card-title">Cобытия</h5>
						</div>
						<div class="card-body">
							<table id="all-events-mod" class="table table-striped dataTable" style="width: 100%;">
								<thead>
									<tr>
										<th>Время открытия</th>
										<th data-priority="2">Время убийства</th>
										<th data-priority="3"></th>
										<th data-priority="1">Босс</th>
										<th>Открыл/Закрыл</th>
										<th>Статус</th>
										<th>Список участников</th>
										<th data-priority="4">Проверено</th>
										<th data-priority="5">PVP</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-12 col-xl-12">
					<div class="card mb-3">
						<div class="card-header">
							<h5 class="card-title">История выдачи</h5>
						</div>
						<div class="card-body">
							<table id="rare-loot-mod" class="table table-striped dataTable" style="width: 100%;">
								<thead>
									<tr>
										<th>Дата выдачи</th>
										<th data-priority="2">Дата выпадения</th>
										<th data-priority="3"></th>
										<th data-priority="1">Итем</th>
										<th>Босс</th>
										<th>Подняли</th>
										<th>Кому</th>
										<th data-priority="4">Выдано</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-12 col-xl-12">
					<div class="card mb-3">
						<div class="card-header">
							<h5 class="card-title">Рейтинг игроков (Средний ЗСС по серверу: <?= $data[0]['defence'] . " " . $data[0]['reduction'] . " " . $data[0]['resistance'] ?>)</h5>
						</div>
						<div class="card-body">
							<table id="mod-ratings" class="table table-striped dataTable" style="width: 100%;">
								<thead>
									<tr>
										<th data-priority="2"></th>
										<th data-priority="1">Игрок</th>
										<th data-priority="5">Класс</th>
										<th>Сервер</th>
										<th>Клан</th>
										<th data-priority="6">КП</th>
										<th data-priority="7">PS</th>
										<th data-priority="8">ЛВЛ</th>
										<th data-priority="4">Прайм</th>
										<th>ЗЩ</th>
										<th>СН</th>
										<th>СПР</th>
										<th>Печать</th>
										<th style="width:80px">Герои</th>
										<th style="width:80px">Агаты</th>
										<th>Колы</th>
										<th data-priority="3">Трансфер</th>
										<th>Боссы</th>
										<th data-priority="1">Эпики</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<?php if ($_SESSION['user']['dpl']) { ?>
					<div class="col-md-12 col-xl-12">
						<div class="card mb-3">
							<div class="card-header">
								<h5 class="card-title">История начислений</h5>
							</div>
							<div class="card-body">
								<table id="points-history" class="table table-striped dataTable" style="width: 100%;">
									<thead>
										<tr>
											<th>Время</th>
											<th>Игрок</th>
											<th>Админ</th>
											<th>Очки</th>
											<th>Комментарий</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</main>
<?php } ?>