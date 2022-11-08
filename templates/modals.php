<?php if ($_SESSION['user']['dpl']) { ?>
	<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasDipl" aria-labelledby="offcanvasDiplEvent" style="visibility: hidden;" aria-hidden="true">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title" id="offcanvasDiplEvent">Панель Дипломата</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Начислить очки</h4>
				<form>
					<input type="hidden" name="action" value="pointsall">
					<div class="mb-3">
						<label class="form-label">Выберите игрока:</label>
						<select name="users_all" id="pointsall-parties" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Количество очков (отрицательное в случае штрафа): </label>
						<input name="points" type="text" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Комментарий:</label>
						<textarea name="comment" class="form-control" rows="2" style="resize: none;"></textarea>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" style="position: relative;" class="btn btn-primary btn-block">Начислить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Добавить предмет</h4>
				<form>
					<input type="hidden" name="action" value="additem">
					<div class="mb-3">
						<label class="form-label">Введите название:</label>
						<input name="name" type="text" value="" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите редкость:</label>
						<select name="itemrarity" id="additem-itemrarity" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите тип:</label>
						<select name="itemtype" id="additem-itemtype" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите класс (для скиллов и оружия):</label>
						<select name="itemclass" id="additem-itemclass" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите иконку:</label>
						<select name="icons" id="additem-4" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Добавить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Создать клан</h4>
				<form>
					<input type="hidden" name="action" value="addclan">
					<div class="mb-3">
						<label class="form-label">Введите название:</label>
						<input name="clan" type="text" value="" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Введите аббревиатуру клана:</label>
						<input name="code" type="text" maxlength="3" value="" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите сервер:</label>
						<select name="servers_ds" id="addclan-servers" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Записать</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Удалить клан</h4>
				<form>
					<input type="hidden" name="action" value="removeclan">
					<div class="mb-3">
						<label class="form-label">Выберите сервер:</label>
						<select name="clans_all" id="removeclan-clans" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Удалить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Создать боевую группу</h4>
				<form>
					<input type="hidden" name="action" value="createcp">
					<div class="mb-3">
						<label class="form-label">Введите название:</label>
						<input name="cpn" type="text" value="" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите сервер:</label>
						<select name="servers_ds" id="createcp-servers" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Создать</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Внести в боевую группу</h4>
				<form>
					<input type="hidden" name="action" value="addcpall">
					<div class="mb-3">
						<label class="form-label">Выберите пользователя:</label>
						<select name="users_all" id="addcpall-user" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите боевую группу:</label>
						<select name="parties_all" id="addcpall-party" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Внести</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Удалить боевую группу</h4>
				<form>
					<input type="hidden" name="action" value="delcpall">
					<div class="mb-3">
						<label class="form-label">Выберите боевую группу:</label>
						<select name="parties_all" id="delcpall-party" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Удалить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Выгнать из боевой группы</h4>
				<form>
					<input type="hidden" name="action" value="remcpall">
					<div class="mb-3">
						<label class="form-label">Выберите пользователя:</label>
						<select name="users_all" id="remcpall-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Выгнать</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Внести драйвера</h4>
				<form>
					<input type="hidden" name="action" value="adddraiv">
					<div class="mb-3">
						<label class="form-label">Пользователь:</label>
						<select name="users_all" id="adddraiv-user" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Драйвер:</label>
						<select name="users_all_add" id="adddraiv-user_add" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Внести</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Удалить драйвера</h4>
				<form>
					<input type="hidden" name="action" value="remdraiv">
					<div class="mb-3">
						<label class="form-label">Выберите пользователя:</label>
						<select name="users_dr" id="remdraiv-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Удалить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Удалить данные</h4>
				<form>
					<input type="hidden" name="action" value="delete">
					<div class="mb-3">
						<label class="form-label">Выберите сервер:</label>
						<select name="servers_ds" id="delete-servers" class="form-control"></select>
					</div>
					<div class="mb-3">
						<div class="form-check form-switch">
							<input name="cp_delete" class="form-check-input" type="checkbox">
							<label class="form-check-label">Удалить все боевые группы</label>
						</div>
					</div>
					<div class="mb-3">
						<div class="form-check form-switch">
							<input name="clan_delete" class="form-check-input" type="checkbox">
							<label class="form-check-label">Удалить все кланы</label>
						</div>
					</div>
					<div class="mb-3">
						<div class="form-check form-switch">
							<input name="boss_delete" class="form-check-input" type="checkbox">
							<label class="form-check-label">Удалить всю статистику боссов</label>
						</div>
					</div>
					<div class="mb-3">
						<div class="form-check form-switch">
							<input name="dkp_delete" class="form-check-input" type="checkbox">
							<label class="form-check-label">Обнулить очки ДКП</label>
						</div>
					</div>
					<div class="mb-3">
						<div class="form-check form-switch">
							<input name="ppl_delete" class="form-check-input" type="checkbox">
							<label class="form-check-label">Деактивировать все учетные записи</label>
						</div>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Удалить</button>
					</div>
				</form>
				<hr />
			</section>
		</div>
	</div>
<?php } ?>

<?php if ($_SESSION['user']['dpl'] || $_SESSION['user']['adm'] || $_SESSION['user']['md']) { ?>
	<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMod" aria-labelledby="offcanvasModEvent" style="visibility: hidden;" aria-hidden="true">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title" id="offcanvasModEvent">Панель Модератора</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Начислить очки</h4>
				<form>
					<input type="hidden" name="action" value="points">
					<div class="mb-3">
						<label class="form-label">Выберите игрока:</label>
						<select name="users" id="points-parties" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Количество очков:</label>
						<input name="points" type="text" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Комментарий:</label>
						<textarea name="comment" class="form-control" rows="2" style="resize: none;"></textarea>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" style="position: relative;" class="btn btn-primary btn-block">Начислить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Добавить игрока в событие</h4>
				<form>
					<input type="hidden" name="action" value="toevent">
					<div class="mb-3">
						<label class="form-label">Выберите событие:</label>
						<select name="cevents" id="toevent-event" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите игрока:</label>
						<select name="users" id="toevent-parties" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Добавить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Удалить игрока из события</h4>
				<form>
					<input type="hidden" name="action" value="fromevent">
					<div class="mb-3">
						<label class="form-label">Выберите событие:</label>
						<select name="cevents" id="fromevent-event" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите игрока для штрафа:</label>
						<select name="users" id="fromevent-parties" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите игрока для поощрения:</label>
						<select name="users_add" id="fromevent-parties-add" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Удалить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Выдать лут</h4>
				<form>
					<input type="hidden" name="action" value="loot">
					<div class="mb-3">
						<label class="form-label">Выберите лут:</label>
						<select name="loot" id="loot-items" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите конечного получателя:</label>
						<select name="users" id="loot-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Выдать</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Изменить поднявшего лут</h4>
				<form>
					<input type="hidden" name="action" value="changeuser">
					<div class="mb-3">
						<label class="form-label">Выберите лут:</label>
						<select name="rloot" id="change-loot" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите того кто поднял лут:</label>
						<select name="clans_ds" id="change-clan" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Изменить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Изменить получателя лута</h4>
				<form>
					<input type="hidden" name="action" value="changeres">
					<div class="mb-3">
						<label class="form-label">Выберите лут:</label>
						<select name="aloot" id="changeres-loot" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите нового получателя лута:</label>
						<select name="users" id="changeres-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Изменить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Внести лут боссу</h4>
				<form>
					<input type="hidden" name="action" value="bossloot">
					<div class="mb-3">
						<label class="form-label">Выберите босса:</label>
						<select name="bosses" id="bossloot-boss" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите лут:</label>
						<select name="items" id="bossloot-loot" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Внести</button>
					</div>
				</form>
				<hr />
			</section>
			<?php if (date("Y-m-d H:i:s") >= date("Y-m-d 00:00:00", strtotime("Wednesday")) && date("Y-m-d H:i:s") <= date("Y-m-d 08:00:00", strtotime("Wednesday"))) { ?>
				<section>
					<span class="bonus-pts">Успех!</span>
					<h4 style="text-decoration: underline;">Произвести рестарт</h4>
					<form>
						<input type="hidden" name="action" value="restart">
						<div class="mb-3">
							<label class="form-label">Введите точное время запуска серверов (<span style="text-decoration: underline;">по МСК</span>):</label>
							<div class="row">
								<div class="col">
									<input name="date" id="datepicker-restart" class="datepicker" value="<?= date('Y/m/d'); ?>" type="text">
								</div>
								<div class="col">
									<input name="time" id="timepicker-restart" class="timepicker" value="<?= date('H:i'); ?>" type="text">
								</div>
							</div>
						</div>
						<div class="d-grid gap-2">
							<button type="submit" style="position: relative;" class="btn btn-primary btn-block">Произвести</button>
						</div>
					</form>
					<hr />
				</section>
			<?php } ?>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Удалить душу игрока</h4>
				<form>
					<input type="hidden" name="action" value="remsoul">
					<div class="mb-3">
						<label class="form-label">Выберите лут:</label>
						<select name="uitems" id="remsoul-loot" class="form-control"></select>
					</div>
					<div class="mb-3">
						<label class="form-label">Выберите игрока:</label>
						<select name="users" id="remsoul-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Удалить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Скрыть игрока из системы</h4>
				<form>
					<input type="hidden" name="action" value="ban">
					<div class="mb-3">
						<label class="form-label">Выберите игрока:</label>
						<select name="users" id="ban-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" style="position: relative;" class="btn btn-primary btn-block">Скрыть</button>
					</div>
				</form>
				<hr />
			</section>
		</div>
	</div>
<?php } ?>

<?php if ($_SESSION['user']['party']) { ?>
	<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasConst" aria-labelledby="offcanvasConstEvent" style="visibility: hidden;" aria-hidden="true">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title" id="offcanvasConstEvent">Панель Боевой Группы</h5>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body">
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Сменить название группы</h4>
				<form>
					<input type="hidden" name="action" value="rencp">
					<div class="mb-3">
						<label class="form-label">Введите название:</label>
						<input name="cpn" type="text" value="" class="form-control">
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Сменить</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Внести в группу</h4>
				<form>
					<input type="hidden" name="action" value="addcp">
					<div class="mb-3">
						<label class="form-label">Выберите пользователя:</label>
						<select name="users" id="addcp-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Внести</button>
					</div>
				</form>
				<hr />
			</section>
			<section>
				<span class="bonus-pts">Успех!</span>
				<h4 style="text-decoration: underline;">Выгнать из группы</h4>
				<form>
					<input type="hidden" name="action" value="remcp">
					<div class="mb-3">
						<label class="form-label">Выберите пользователя:</label>
						<select name="users_cp" id="remcp-user" class="form-control"></select>
					</div>
					<div class="d-grid gap-2">
						<button type="submit" class="btn btn-primary btn-block">Выгнать</button>
					</div>
				</form>
				<hr />
			</section>
		</div>
	</div>
<?php } ?>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAdmin" aria-labelledby="offcanvasAdminEvent" style="visibility: hidden;" aria-hidden="true">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="offcanvasAdminEvent">Панель Событий</h5>
		<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<section>
			<span class="bonus-pts">Успех! (+20 pts.)</span>
			<h4 style="text-decoration: underline;">Создать событие</h4>
			<form>
				<input type="hidden" name="action" value="pubevents">
				<div class="mb-3">
					<label class="form-label">Выберите босса:</label>
					<select name="bosses" id="pubevents-boss" class="form-control"></select>
				</div>
				<div class="mb-3">
					<label class="form-label">Введите точное время убийства босса (<span style="text-decoration: underline;">по МСК</span>):</label>
					<div class="row">
						<div class="col">
							<input name="date" id="datepicker-pubevents" class="datepicker" value="<?= date('Y/m/d'); ?>" type="text">
						</div>
						<div class="col">
							<input name="time" id="timepicker-pubevents" class="timepicker" value="<?= date('H:i'); ?>" type="text">
						</div>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label"><i>Выберите клан поднявший лут (если был):</i></label>
					<select name="clans_ds" id="pubevents-clans" class="form-control"></select>
				</div>
				<div class="mb-3">
					<label class="form-label"><i>Выберите весь выпавший лут (если был):</i></label>
					<select name="bdrop[]" id="pubevents-items" class="form-control" multiple></select>
				</div>
				<div class="mb-3">
					<div class="form-check form-switch">
						<input name="awakened" class="form-check-input" type="checkbox">
						<label class="form-check-label">Пробужденный босс</label>
					</div>
				</div>
				<div class="mb-3">
					<div class="form-check form-switch">
						<input name="nokill" class="form-check-input" type="checkbox">
						<label class="form-check-label">Босса убил вражеский альянс (событие будет изначально закрытым, в нем нельзя будет отмечаться и использоваться оно будет только для таймингов респауна)</label>
					</div>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Создать</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Поправить время события</h4>
			<form>
				<input type="hidden" name="action" value="timechange">
				<div class="mb-3">
					<label class="form-label">Введите точное время убийства босса (<span style="text-decoration: underline;">по МСК</span>):</label>
					<div class="row">
						<div class="col">
							<input name="date" id="datepicker_tc" class="datepicker" value="<?= date('Y/m/d'); ?>" type="text">
						</div>
						<div class="col">
							<input name="time" id="timepicker_tc" class="timepicker" value="<?= date('H:i'); ?>" type="text">
						</div>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Выберите событие:</label>
					<select name="cevents" id="timechange-event" class="form-control"></select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Поправить</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Добавить лут в событие</h4>
			<form>
				<input type="hidden" name="action" value="dropadd">
				<div class="mb-3">
					<label class="form-label">Выберите событие:</label>
					<select name="cevents" id="dropadd-event" class="form-control"></select>
				</div>
				<div class="mb-3">
					<label class="form-label">Выберите того кто поднял лут:</label>
					<select name="clans_ds" id="dropadd-clan" class="form-control"></select>
				</div>
				<div class="mb-3">
					<label class="form-label">Выберите весь выпавший лут:</label>
					<select name="drop[]" id="dropadd-items" class="form-control" multiple></select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Добавить</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Удалить лут из события</h4>
			<form>
				<input type="hidden" name="action" value="remdrop">
				<div class="mb-3">
					<label class="form-label">Выберите лут:</label>
					<select name="floot" id="remove-loot" class="form-control"></select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Удалить</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Удалить событие</h4>
			<form>
				<input type="hidden" name="action" value="delevent">
				<div class="mb-3">
					<label class="form-label">Выберите событие:</label>
					<select name="cevents" id="delete-event" class="form-control"></select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Удалить</button>
				</div>
			</form>
			<hr />
		</section>
	</div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasPlayer" aria-labelledby="offcanvasPlayerEvent" style="visibility: hidden;" aria-hidden="true">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="offcanvasPlayerEvent">Панель Персонажа</h5>
		<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Изменить данные персонажа</h4>
			<form>
				<input type="hidden" name="action" value="data">
				<div class="mb-3">
					<label class="form-label">Уровень персонажа:</label>
					<input name="level" type="text" value="<?= $_SESSION['user']['level'] ? $_SESSION['user']['level'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Всего коллекций предметов <a target="_blank" rel="noopener noreferrer" href="/img/info_3.png">(смотреть тут)</a>:</label>
					<input name="collections" type="text" value="<?= $_SESSION['user']['collections'] ? $_SESSION['user']['collections'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Всего карт классов <a target="_blank" rel="noopener noreferrer" href="/img/info_1.png">(смотреть тут)</a>:</label>
					<input name="heroes_all" type="text" value="<?= $_SESSION['user']['heroes_all'] ? $_SESSION['user']['heroes_all'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Всего агатионов <a target="_blank" rel="noopener noreferrer" href="/img/info_2.png">(смотреть тут)</a>:</label>
					<input name="agations_all" type="text" value="<?= $_SESSION['user']['agations_all'] ? $_SESSION['user']['agations_all'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Количество красных карт классов:</label>
					<input name="heroes_hr" type="text" value="<?= $_SESSION['user']['heroes_hr'] ? $_SESSION['user']['heroes_hr'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Количество красных карт агатионов:</label>
					<input name="agations_hr" type="text" value="<?= $_SESSION['user']['agations_hr'] ? $_SESSION['user']['agations_hr'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Количество фиолетовых карт классов:</label>
					<input name="heroes_lg" type="text" value="<?= $_SESSION['user']['heroes_lg'] ? $_SESSION['user']['heroes_lg'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Количество фиолетовых карт агатионов:</label>
					<input name="agations_lg" type="text" value="<?= $_SESSION['user']['agations_lg'] ? $_SESSION['user']['agations_lg'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Защита (под магаз. баффом):</label>
					<input name="defence" type="text" value="<?= $_SESSION['user']['defence'] ? $_SESSION['user']['defence'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Снижение урона (под магаз. баффом):</label>
					<input name="reduction" type="text" value="<?= $_SESSION['user']['reduction'] ? $_SESSION['user']['reduction'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Сопротивление умениям (под магаз. баффом):</label>
					<input name="resistance" type="text" value="<?= $_SESSION['user']['resistance'] ? $_SESSION['user']['resistance'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Печать духа <a target="_blank" rel="noopener noreferrer" href="/img/info_5.png">(смотреть тут)</a>:</label>
					<input name="seal" type="text" value="<?= $_SESSION['user']['seal'] ? $_SESSION['user']['seal'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Пробуждение <a target="_blank" rel="noopener noreferrer" href="/img/info_10.png">(смотреть тут)</a>:</label>
					<input name="awakening" type="text" value="<?= $_SESSION['user']['awakening'] ? $_SESSION['user']['awakening'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Души монстров <a target="_blank" rel="noopener noreferrer" href="/img/info_8.png">(смотреть тут)</a>:</label>
					<input name="souls" type="text" value="<?= $_SESSION['user']['souls'] ? $_SESSION['user']['souls'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<label class="form-label">Средний активный праймтайм (1-20 часов)</a>:</label>
					<input name="prime" min="1" min="20" type="text" value="<?= $_SESSION['user']['prime'] ? $_SESSION['user']['prime'] : 0; ?>" class="form-control">
				</div>
				<div class="mb-3">
					<div class="form-check form-switch">
						<input name="ready" class="form-check-input" type="checkbox" <?= $_SESSION['user']['ready'] ? 'checked' : ''; ?>>
						<label class="form-check-label">Отметьте если вы готовы к перелету</label>
					</div>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Изменить</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Записать личные вещи</h4>
			<form>
				<input type="hidden" name="action" value="private">
				<div class="mb-3">
					<label class="form-label">Перечислите все героические и легендарные вещи:</label>
					<select name="pitems[]" id="personal-items" class="form-control" multiple>
						<?php foreach ($_SESSION['user']['items'] as $e) {
							echo ("<option value='{$e['item']}' selected>{$e['name']}</option>");
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label">Перечислите все способности:</label>
					<select name="pskills[]" id="personal-skills" class="form-control" multiple>
						<?php foreach ($_SESSION['user']['skills'] as $e) {
							echo ("<option value='{$e['item']}' selected>{$e['name']}</option>");
						}
						?>
					</select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Записать</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Вишлист</h4>
			<form>
				<input type="hidden" name="action" value="wishlist">
				<div class="mb-3">
					<label class="form-label">Перечислите необходимые предметы:</label>
					<select name="ritems[]" id="wishlist-items" class="form-control" multiple>
						<?php foreach ($_SESSION['user']['wishlist'] as $e) {
							echo ("<option value='{$e['item']}' selected>{$e['name']}</option>");
						}
						?>
					</select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Записать</button>
				</div>
			</form>
			<hr />
		</section>
		<section>
			<span class="bonus-pts">Успех!</span>
			<h4 style="text-decoration: underline;">Часовой пояс</h4>
			<form>
				<input type="hidden" name="action" value="timezone">
				<div class="mb-3">
					<label class="form-label">Выберите часовой пояс:</label>
					<select name="timezone" id="timezone-items" class="form-control">
						<?php
						echo ("<option value='{$_SESSION['user']['timezone']['id']}' selected>{$_SESSION['user']['timezone']['label']}</option>");
						?>
					</select>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" class="btn btn-primary btn-block">Сохранить</button>
				</div>
			</form>
			<hr />
		</section>
	</div>
</div>

<!-- Модал списков -->

<div class="modal fade" id="userModal" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-body m-3">
				<p id="userInner" class="mb-0"></p>
			</div>
		</div>
	</div>
</div>

<!-- Модал фильтра инвентаря -->

<div class="modal fade" id="itemModal" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Фильтр инвентаря</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body m-3">
				<div class="mb-3">
					<label class="form-label">Выберите кристалл:</label>
					<select name="uitems" id="items-modal" class="form-control"></select>
				</div>
				<div class="mb-3">
					<label class="form-label"><i>Выберите клан:</i></label>
					<select name="clans_ds" id="citems-modal" class="form-control"></select>
				</div>
				<div class="d-grid gap-2">
					<button type="button" data-dismiss="modal" onclick="searchItems(document.getElementById('items-modal').value, document.getElementById('citems-modal').value);" class="btn btn-primary btn-block">Поиск</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Модал фильтра инвентаря -->

<div class="modal fade" id="loadModal" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Загрузить подтверждение</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body m-3">
				<input type="hidden" name="loot" id="loot-upload" value="">
				<div class="mb-3">
					<input class="form-control" type="file" accept="image/png, image/bmp, image/jpeg" id="confirmation-image">
				</div>
				<div class="d-grid gap-2">
					<button type="button" data-dismiss="modal" onclick="upload(document.getElementById('loot-upload').value, document.getElementById('confirmation-image').files[0]);" class="btn btn-primary btn-block">Сохранить</button>
				</div>
			</div>
		</div>
	</div>
</div>