<main class="content">
	<div class="container-fluid p-0">
		<?php if ($_SESSION['error']) {
			$info = $templates['logerror'];
			echo str_replace('{{info}}', $_SESSION['error'], $info);
			unset($_SESSION['error']);
		}  ?>

		<?php if ($_SESSION['registration']) { ?>
			<div class="row">
				<div class="col-md-12 col-xl-12">
					<div class="card mb-3">
						<div class="card-body">
							<form id="gateway">
								<input type="hidden" name="action" value="const">
								<div class="row">
									<div class="mb-3 col-md-4">
										<label class="form-label">Сервер:</label>
										<select name="servers_ds" id="servers_ds" class="form-control"></select>
									</div>
									<div class="mb-3 col-md-4">
										<label class="form-label">Клан:</label>
										<select name="clans_ds" id="clans_ds" class="form-control"></select>
									</div>
									<div class="mb-3 col-md-4">
										<label class="form-label">Класс:</label>
										<select name="classes_ds" id="classes_ds" class="form-control"></select>
									</div>
								</div>
								<div class="row">
									<div class="mb-3 col-md-4">
										<label class="form-label">Новое КП:</label>
										<input type="text" class="form-control" name="parties_new" id="parties_new">
									</div>
									<div class="mb-3 col-md-4">
										<label class="form-label">Существующее КП:</label>
										<select name="parties_ds" id="parties_ds" class="form-control"></select>
									</div>
									<div class="mb-3 col-md-4">
										<label class="form-label">Внутриигровой ник:</label>
										<input type="text" class="form-control" name="nick" id="nick" maxlength="16" pattern="^[a-zA-Zа-яА-Я0-9*]+$">
									</div>
									<script>
									</script>
								</div>
								<div class="d-grid gap-2">
									<button type="submit" class="btn btn-primary btn-block">Сохранить</button>
								</div>
							</form>
							<span class="blink-soft" style="display:none">Соединяюсь с серверами Discord посредством Sacred Bot, это может занять до одной минуты. Пожалуйста не перезагружайте страницу, вас автоматически перенаправит после завершения всех операций.</span>
						</div>
					</div>
				</div>
			</div>
		<?php }
		unset($_SESSION['registration']); ?>

		<div class="mb-3">
			<h1 class="h3 d-inline align-middle">Добро пожаловать в систему учета TDKP!</h1>
		</div>
		<div class="row">
			<div class="col-md-12 col-xl-12">
				<div class="card mb-3">
					<div class="card-body">
						<p>Данное приложение основано на системе ДКП и служит для учета активности внутри альянсов Lineage 2M. Базовые функции системы TDKP:</p>
						<ul>
							<li>Мониторинг активности членов КП и индивидуальных игроков</li>
							<li>Система штрафов и поощрений на основе ДКП очков</li>
							<li>Помощь в распределении снаряжения внутри альянса</li>
							<li>Упрощенное слежение за временем респауна боссов</li>
						</ul>
						<p>Для начала работы пожалуйста войдите в систему с помощью учетной записи Discord.</p>

						<img src="/img/spreview.png" class="pimg">
					</div>
				</div>
			</div>
		</div>
	</div>
</main>