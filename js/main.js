"use strict";

document.addEventListener("DOMContentLoaded", function () {
	var scrollbar = window.Scrollbar;
	document.getElementById("history") && scrollbar.init(document.getElementById("history"), {});
	document.getElementById("modhistory") && scrollbar.init(document.getElementById("modhistory"), {});
	$.fn.dataTable.moment('DD.MM HH:mm');

	/* Таблицы */
	var tables = []; var table_els = document.querySelectorAll('.dataTable');

	for (var i = 0; i < table_els.length; ++i) {
		var order = [[0, "desc"]];
		var paging = false;
		var responsive = true;
		var info = false;
		var scroller = true;
		var defs = [];
		var dom = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
			"<'row'<'col-sm-12'tr>>" +
			"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";

		switch (table_els[i].id) {
			case "rare-loot":
				defs = [{ type: 'natural', targets: [6, 7] }];
				break;
			case "boss-events":
				defs = [{ type: 'natural', targets: [4, 5, 6] }];
				order = [[1, "asc"]];
				break;
			case "itemsconst-ratings":
			case "itemsclan-ratings":
				order = [[1, "desc"]];
				break;
			case "arc-rare-loot":
				defs = [{ type: 'natural', targets: [6, 7] }];
				paging = true;
				info = true;
				scroller = false;
				break;
			case "admin-ratings":
			case "mod-ratings":
				defs = [{ type: 'natural', targets: [8] }];
				order = [[6, "desc"]];
				paging = true;
				info = true;
				scroller = false;
				dom = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
					"<'row'<'col-sm-12'tr>>" +
					"<'row'<'col-sm-12 col-md-5'B><'col-sm-12 col-md-7'p>>";
				break;
			case "boss-ratings":
				order = [[3, "desc"]];
				break;
			case "items-ratings":
			case "bossconst-ratings":
			case "bossclan-ratings":
				order = [[2, "desc"]];
				break;
			case "ps-ratings":
			case "bonus-ratings":
			case "cards-ratings":
			case "cols-ratings":
			case "dkp-ratings":
			case "knifes-ratings":
			case "mages-ratings":
			case "archers-ratings":
			case "orbs-ratings":
			case "tanks-ratings":
			case "gladiators-ratings":
			case "warlords-ratings":
			case "cardsconst-ratings":
			case "colsconst-ratings":
			case "all-bl":
				order = [[0, "asc"]];
				break;
			case "souls":
				responsive = false;
				break;
		}

		tables[table_els[i].id] = new DataTable("#" + table_els[i].id, {
			ajax: {
				url: '/bin/tables.php?action=' + table_els[i].id,
			},
			language: {
				url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json'
			},
			dom: dom,
			buttons: [
				'excel'
			],
			scrollX: true,
			paging: paging,
			info: info,
			scrollX: true,
			searching: true,
			scrollY: 500,
			deferRender: true,
			responsive: responsive,
			scroller: scroller,
			order: order,
			columnDefs: defs,
			initComplete: function (settings, json) {
				var tag = document.createElement("h5");
				var content = document.createTextNode(this.closest('.card.mb-3')[0].querySelectorAll('h5')[0].textContent);

				tag.appendChild(content);
				tag.classList.add("card-header");
				tag.classList.add("table-title");

				this.closest('.dataTables_wrapper')[0]
					.querySelectorAll('div.col-sm-12.col-md-6:first-child')[0]
					.appendChild(tag);

				this.closest('.card.mb-3')[0].querySelectorAll(".card-header")[0].remove();

				scrollbar.init(this.closest(".dataTables_scrollBody")[0], { alwaysShowTracks: true, });

				try {
					document.getElementById("all-bl_filter").innerHTML = '<button style="min-width: 125px;" data-bs-toggle="modal" data-bs-target="#itemModal" class="btn btn-danger"><span>Поиск</span></button>';
				} catch { }
			}
		});
	}

	setInterval(function () {
		table_els.forEach(function (item) {
			switch (item.id) {
				case "all-events":
				case "boss-events":
					tables[item.id].ajax.reload();
					break;
			}
		});
	}, 15000);

	/* Фикс заголовка таблиц */
	window.addEventListener('resize', function (event) {
		setTimeout(function () {
			$.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw();
		}, 350);
	}, true);

	/* Селекты и ивенты селектов */
	var selects = []; var select_els = document.querySelectorAll('select');

	for (var i = 0; i < select_els.length; ++i) {
		if (select_els[i].id) {
			if (select_els[i].id == "wishlist-items")
				var limit = 5;
			else
				var limit = -1;

			selects[select_els[i].id] = new Choices("#" + select_els[i].id, {
				removeItemButton: true,
				shouldSort: false,
				duplicateItemsAllowed: false,
				maxItemCount: limit,
				maxItemText: (maxItemCount) => {
					return `Можно добавить только ${maxItemCount} вещей`;
				},
			});
		}
	}

	for (var key in selects) {
		selects[key].passedElement.element.addEventListener(
			'showDropdown',
			function (event) {
				selects[this.id].setChoices([{}], 'value', 'label', true);
				selects[this.id].setChoices(async () => {
					try {
						var items = {};

						if (this.id == "drop-user" || this.id == "drop-items") {
							var e = selects['drop-event'].passedElement.element;
							var v = 0;

							if (e.selectedIndex != '-1') {
								v = e.options[e.selectedIndex].value;
							}

							items = await fetch('bin/dropdowns.php?g=' + this.name.replace(/[\[\]]/g, '') + '&o=' + v);
						} else if (this.id == "pubevents-user" || this.id == "pubevents-items") {
							var e = selects['pubevents-boss'].passedElement.element;
							var v = 0;

							if (e.selectedIndex != '-1') {
								v = e.options[e.selectedIndex].value;
							}

							items = await fetch('bin/dropdowns.php?g=' + this.name.replace(/[\[\]]/g, '') + '&o=' + v);
						} else if (this.id == "dropadd-user" || this.id == "dropadd-items") {
							var e = selects['dropadd-event'].passedElement.element;
							var v = 0;

							if (e.selectedIndex != '-1') {
								v = e.options[e.selectedIndex].value;
							}

							items = await fetch('bin/dropdowns.php?g=' + this.name.replace(/[\[\]]/g, '') + '&o=' + v);
						} else if (this.id == "clans_ds" || this.id == "parties_ds") {
							var e = selects['servers_ds'].passedElement.element;
							var v = 0;

							if (e.selectedIndex != '-1') {
								v = e.options[e.selectedIndex].value;
							}

							items = await fetch('bin/dropdowns.php?g=' + this.name.replace(/[\[\]]/g, '') + '&o=' + v);
						} else if (this.id == "movecp-clans") {
							var e = selects['movecp-servers'].passedElement.element;
							var v = 0;

							if (e.selectedIndex != '-1') {
								v = e.options[e.selectedIndex].value;
							}

							items = await fetch('bin/dropdowns.php?g=' + this.name.replace(/[\[\]]/g, '') + '&o=' + v);
						} else {
							items = await fetch('bin/dropdowns.php?g=' + this.name.replace(/[\[\]]/g, ''));
						}
						return items.json();
					} catch (err) {
						console.error(err);
					}
				});
			},
			false,
		);

		if (key == 'parties_ds') {
			selects[key].passedElement.element.addEventListener(
				'addItem',
				function (event) {
					var e = selects[this.id].passedElement.element;
					if (e.options[e.selectedIndex].value) {
						document.getElementById("parties_new").disabled = true;
						document.getElementById("parties_new").value = "";
					}
				},
				false,
			);

			selects[key].passedElement.element.addEventListener(
				'removeItem',
				function (event) {
					var e = selects[this.id].passedElement.element;
					if (e.selectedIndex == '-1') {
						document.getElementById("parties_new").disabled = false;
					}
				},
				false,
			);
		}
	}

	/* Клики на свич */
	document.getElementsByTagName('body')[0].addEventListener('change', (event) => {
		if (event.target.classList.contains("evntcheck") || event.target.classList.contains("lootcheck") || event.target.classList.contains("soulcheck")) {
			event.target.disabled = true;
			checked(event.target.id);
		}
		if (event.target.classList.contains("pvpcheck")) {
			checked(event.target.id);
		}
		if (event.target.classList.contains("rescheck")) {
			checked(event.target.id);
		}
	});

	/* Плагинc дат */
	$('.datepicker').periodpicker({
		norange: true,
		cells: [1, 1],
		withoutBottomPanel: true,
		yearsLine: false,
		title: false,
		resizeButton: false,
		closeButton: false,
		fullsizeButton: false,
		fullsizeOnDblClick: false,
		lang: 'ru',
		formatDecoreDateWithYear: 'DD.MM.YYYY'
	});

	$('.timepicker').timepickeralone({
		inputFormat: 'HH:mm',
		hours: true,
		minutes: true,
		seconds: false,
		twelveHoursFormat: false,
		ampm: false,
	});

	/* Логика форм */
	for (var i = 0; i < document.forms.length; i++) {
		var form = document.forms[i];
		form.addEventListener('submit', (e) => {
			e.preventDefault();

			let con = confirm("Вы уверены в правильности заполненных данных?");

			if (con) {
				postData('/bin/admin.php', new FormData(e.currentTarget), () => {
					var bonusEl = e.target.parentElement.querySelector('.bonus-pts');
					if (bonusEl) bonusEl.style.opacity = "1";
				});

				if (e.currentTarget.querySelector('input[name="action"]').value != 'data'
					&& e.currentTarget.querySelector('input[name="action"]').value != 'wishlist'
					&& e.currentTarget.querySelector('input[name="action"]').value != 'private'
					&& e.currentTarget.querySelector('input[name="action"]').value != 'bossloot'
					&& e.currentTarget.querySelector('input[name="action"]').value != 'restart')
					e.currentTarget.reset();

				if (e.currentTarget.querySelector('input[name="action"]').value == 'const') {
					document.querySelector('.blink-soft').style.display = 'block';
					document.getElementById("parties_new").disabled = false;
					e.currentTarget.querySelector('button').disabled = true;
				}
			}
		});
	}

	/* Затухание бонусов */
	var bonus = document.querySelectorAll(".bonus-pts");
	for (var i = 0; i < bonus.length; i++) {
		bonus[i].addEventListener("transitionend", function () {
			if (this.style.opacity == 1) this.style.opacity = 0;
		}, false);
	}

	/* Ресайз истории */
	const params = new Proxy(new URLSearchParams(window.location.search), {
		get: (searchParams, prop) => searchParams.get(prop),
	});

	if (params.p == 'profile') {
		window.onload = window.onresize = function () {
			var picturebox = document.getElementById('picturebox');
			var wavebox = document.getElementById('wavebox');
			var constbox = document.getElementById('constbox');

			var history = document.getElementById('history');
			var loothistory = document.getElementById('loothistory');
			var modhistory = document.getElementById('modhistory');

			if (picturebox.offsetHeight - 70 != history.offsetHeight) {
				history.style.height = picturebox.offsetHeight - 70 + "px";
			}
			if (wavebox.offsetHeight - 70 != loothistory.offsetHeight) {
				loothistory.style.height = wavebox.offsetHeight - 70 + "px";
			}
			if (constbox.offsetHeight - 70 != modhistory.offsetHeight) {
				modhistory.style.height = constbox.offsetHeight - 70 + "px";
			}
		}
	}
});

function checked(id) {
	var formData = new FormData();
	var options = id.split("-");

	formData.append('action', options[0]);
	formData.append('id', options[1]);
	formData.append('uid', options[2]);
	formData.append('status', document.querySelector('#' + id).checked);

	fetch('bin/admin.php', {
		credentials: "same-origin",
		method: 'POST',
		body: formData,
	}).then(response => response.text()).then((text) => {
		if (text != "Success")
			alert(text);
	});
}

function attend(id) {
	var formData = new FormData();
	var options = id.split("-");

	formData.append('action', options[0]);
	formData.append('id', options[1]);
	formData.append('status', options[2]);

	if (options[2] == 'add') {
		document.getElementById(id).innerHTML = "Не участвовал";
		document.getElementById(id).id = id.replace('add', 'remove');
	} else if (options[2] == 'remove') {
		document.getElementById(id).innerHTML = "Участвовал";
		document.getElementById(id).id = id.replace('remove', 'add');
	}

	fetch('bin/admin.php', {
		credentials: "same-origin",
		method: 'POST',
		body: formData,
	}).then(response => response.text()).then((text) => {
		if (text != "Success")
			alert(text);
	});
}

function show(id) {
	var formData = new FormData();
	var options = id.split("-");

	if (options[0] == 'ld') {
		document.getElementById("loot-upload").value = options[1];
		return;
	}

	formData.append('action', options[0]);
	formData.append('id', options[1]);

	fetch('bin/admin.php', {
		credentials: "same-origin",
		method: 'POST',
		body: formData,
	}).then(response => response.text()).then((text) => {
		document.getElementById("userInner").innerHTML = "";
		document.getElementById("userInner").innerHTML = text;
	});
}

/* Отметка о полученном луте и загрузка изображения */
function upload(id, file) {
	var formData = new FormData();

	formData.append('action', 'imgupload');
	formData.append('id', id);
	formData.append('file', file);

	fetch('bin/admin.php', {
		credentials: "same-origin",
		method: 'POST',
		body: formData,
	}).then(response => response.text()).then((text) => {
		bootstrap.Modal.getInstance(document.getElementById('loadModal')).hide();
		if (text == "Success") {
			document.getElementById("cl-" + id).checked = true;
			alert("Подтверждение загружено успешно!");
		} else
			alert(text);

	});
}

/* Аналог аякса для форм */
function postData(path, data, callback) {
	fetch(path, {
		credentials: "same-origin",
		method: 'POST',
		body: data
	})
		.then(function (response) {
			if (response.ok) {
				return response.text();
			}

			throw new Error('Something went wrong.');
		})
		.then(function (text) {
			if (text == 'Discord data was changed successfully') {
				window.location.href = '/?p=gateway&a=login';
			} else if (text != 'Success') {
				try { document.querySelector('button[type="submit"]').disabled = false; document.querySelector('.blink-soft').style.display = 'none'; } catch { };
				alert('Произошла ошибка, запрос не был обработан. Обратитесь к администратору.\n\n' + text);
			} else {
				if (callback) callback();
			}
		})
		.catch(function (error) {
			try { document.querySelector('button[type="submit"]').disabled = false; document.querySelector('.blink-soft').style.display = 'none'; } catch { };
			alert('Произошла ошибка, запрос не был обработан. Обратитесь к администратору.\n\n' + error);
		});
}

/* Поиск предметов по клану */
function searchItems(id, cid) {
	bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();

	if (!id) return;

	$('#all-bl').DataTable().ajax.url("/bin/tables.php?action=all-bl&i=" + id + "&clan=" + cid).load();
}

/* Апдейт времени */
function time() {
	var time = new Date();

	document.getElementById("timepicker-pubevents").value = time.toLocaleString('en-GB', { timeZone: 'Europe/Moscow', timeStyle: 'short' });
	document.getElementById("timepicker_tc").value = time.toLocaleString('en-GB', { timeZone: 'Europe/Moscow', timeStyle: 'short' });
}