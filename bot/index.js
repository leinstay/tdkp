const express = require('express');
const https = require('https');
const app = express();

const config = require("./config.json");

const mysql = require('mysql');
const connection = mysql.createConnection({
	host: config.host,
	user: config.user,
	password: config.password,
	database: config.database,
	supportBigNumbers: true
});

connection.connect();

const { Client, Intents } = require('discord.js');
const ds = new Client({ disableEveryone: false, intents: new Intents(32767) });

ds.once('ready', () => {
	let guild = ds.guilds.cache.get(config.unid);

	/* Управление дискордом из TDKP */
	app.get('/api', (req, res) => {
		manageRoles(guild, req, res);
	});

	app.get('/message', (req, res) => {
		sendEmbed(guild, req, res);
	});

	app.listen(3000, function () {
		console.log('Bot listening on *:3000');
	});

	setInterval(async function () {
		connection.query("UPDATE `users` a LEFT JOIN( SELECT `user`, COUNT(`user`) AS total_bosses FROM `attendance` JOIN `users` ON `users`.`id` = `user` WHERE `attendance`.`server` = `users`.`server` GROUP BY `user`) b ON a.`id` = b.`user` LEFT JOIN ( SELECT `user`, COUNT(`user`) AS total_bosses FROM `attendance` JOIN `users` ON `users`.`id` = `user` JOIN `bosses` ON `attendance`.`boss` = `bosses`.`id` WHERE `attendance`.`server` = `users`.`server` AND `bosses`.`dkp` >= 35 GROUP BY `user` ) f ON a.`id` = f.`user` LEFT JOIN ( SELECT `user`, COUNT(`user`) AS total_bosses FROM `attendance` JOIN `users` ON `users`.`id` = `user` WHERE `attendance`.`server` = `users`.`server` AND `time` >= DATE_ADD( CURDATE(), INTERVAL -7 DAY ) GROUP BY `user` ) d ON a.`id` = d.`user` LEFT JOIN ( SELECT `user`, COUNT(`user`) AS total_bosses FROM `attendance` JOIN `users` ON `users`.`id` = `user` JOIN `bosses` ON `attendance`.`boss` = `bosses`.`id` WHERE `attendance`.`server` = `users`.`server` AND `bosses`.`dkp` >= 35 AND `time` >= DATE_ADD( CURDATE(), INTERVAL -7 DAY ) GROUP BY `user` ) g ON a.`id` = g.`user` LEFT JOIN ( SELECT `user`, COUNT(`user`) AS total_items FROM `loot` JOIN `users` ON `users`.`id` = `user` WHERE `loot`.`server` = `users`.`server` AND `salary` <> 1 GROUP BY `user` ) c ON a.`id` = c.`user` LEFT JOIN ( SELECT `user`, COUNT(`user`) AS total_items FROM `loot` JOIN `users` ON `users`.`id` = `user` WHERE `loot`.`server` = `users`.`server` AND `salary` <> 1 AND `time` >= DATE_ADD( CURDATE(), INTERVAL -7 DAY ) GROUP BY `user` ) e ON a.`id` = e.`user` SET a.`total_bosses` = IFNULL(b.`total_bosses`, 0), a.`total_bosses_last` = IFNULL(d.`total_bosses`, 0), a.`total_bosses_epic` = IFNULL(f.`total_bosses`, 0), a.`total_bosses_epic_last` = IFNULL(g.`total_bosses`, 0), a.`total_items` = IFNULL(c.`total_items`, 0), a.`total_items_last` = IFNULL(e.`total_items`, 0)", async function (error, results, fields) {
			if (error) throw error;
			connection.query("UPDATE `clans` a LEFT JOIN ( SELECT `users`.`clan`, COUNT(DISTINCT `attendance`.`event`) AS total_bosses FROM `attendance` JOIN `users` ON `attendance`.`user` = `users`.`id` WHERE `attendance`.`server` = `users`.`server` GROUP BY `users`.`clan` ) b ON a.`id` = b.`clan` LEFT JOIN ( SELECT `users`.`clan`, COUNT(DISTINCT `attendance`.`event`) AS total_bosses FROM `attendance` JOIN `users` ON `attendance`.`user` = `users`.`id` JOIN `bosses` ON `attendance`.`boss` = `bosses`.`id` WHERE `attendance`.`server` = `users`.`server` AND `bosses`.`dkp` >= 35 GROUP BY `users`.`clan` ) f ON a.`id` = f.`clan` LEFT JOIN ( SELECT `users`.`clan`, COUNT(DISTINCT `attendance`.`event`) AS total_bosses FROM `attendance` JOIN `users` ON `attendance`.`user` = `users`.`id` WHERE `attendance`.`server` = `users`.`server` AND `time` >= DATE_ADD( CURDATE(), INTERVAL -7 DAY ) GROUP BY `users`.`clan` ) d ON a.`id` = d.`clan` LEFT JOIN ( SELECT `users`.`clan`, COUNT(DISTINCT `attendance`.`event`) AS total_bosses FROM `attendance` JOIN `users` ON `attendance`.`user` = `users`.`id` JOIN `bosses` ON `attendance`.`boss` = `bosses`.`id` WHERE `attendance`.`server` = `users`.`server` AND `bosses`.`dkp` >= 35 AND `time` >= DATE_ADD( CURDATE(), INTERVAL -7 DAY ) GROUP BY `users`.`clan` ) g ON a.`id` = g.`clan` LEFT JOIN ( SELECT `users`.`clan`, COUNT(`users`.`clan`) AS total_items FROM `loot` JOIN `users` ON `loot`.`user` = `users`.`id` WHERE `loot`.`server` = `users`.`server` AND `salary` <> 1 GROUP BY `users`.`clan` ) c ON a.`id` = c.`clan` LEFT JOIN ( SELECT `users`.`clan`, COUNT(`users`.`clan`) AS total_items FROM `loot` JOIN `users` ON `loot`.`user` = `users`.`id` WHERE `loot`.`server` = `users`.`server` AND `salary` <> 1 AND `time` >= DATE_ADD( CURDATE(), INTERVAL -7 DAY ) GROUP BY `users`.`clan` ) e ON a.`id` = e.`clan` SET a.`total_bosses` = IFNULL(b.`total_bosses`, 0), a.`total_bosses_last` = IFNULL(d.`total_bosses`, 0), a.`total_bosses_epic` = IFNULL(f.`total_bosses`, 0), a.`total_bosses_epic_last` = IFNULL(g.`total_bosses`, 0), a.`total_items` = IFNULL(c.`total_items`, 0), a.`total_items_last` = IFNULL(e.`total_items`, 0)", async function (error, results, fields) {
				if (error) throw error;
				https.get('https://justdkp.com/bin/admin.php?action=dkpupdate', (responce) => { });
			});
		});

	}, 5 * 60 * 1000);

	setInterval(async function () {
		https.get('https://justdkp.com/bin/admin.php?action=timeshift', (responce) => { });
	}, 2 * 60 * 1000);
});

ds.on('interactionCreate', async interaction => {
	await interaction.deferReply({ ephemeral: true });

	if (interaction.isButton()) {
		var type = interaction.customId.split("-")[0];
		var id = interaction.customId.split("-")[1];
		var user = interaction.user.id;

		connection.query('SELECT `dsk_id` FROM `drivers` WHERE `adsk_id` = ' + user + ' LIMIT 1', async function (error, results) {
			if (error) throw error;
			if (results.length !== 0)
				user = results[0].dsk_id;
		});

		if (type == "c") {
			connection.query('SELECT `events`.`id`, `bosses`.`id` AS bid, `bosses`.`name` FROM `events` JOIN `bosses` ON `events`.`boss` = `bosses`.`id` WHERE md5(`events`.`id`) = "' + id + '" AND `status` = 0 LIMIT 1', async function (error, results) {
				if (error) throw error;

				if (results.length !== 0) {
					var eid = results[0].id;
					var bid = results[0].bid;
					var bname = results[0].name;

					if (interaction.component.style == "SUCCESS") {
						connection.query('SELECT `users`.`id`, `users`.`clan`, `users`.`server` FROM `users` WHERE `id` = (SELECT `id` FROM `users` WHERE `dsk_id` = "' + user + '" LIMIT 1)', async function (error, results) {
							if (error) throw error;

							var uid = results[0].id;
							var clan = results[0].clan;
							var server = results[0].server;

							if (!Boolean(clan))
								await interaction.editReply({ content: 'Отсутствует критичная для выполнения запроса роль: Клан', ephemeral: true });

							if (!Boolean(server))
								await interaction.editReply({ content: 'Отсутствует критичная для выполнения запроса роль: Сервер', ephemeral: true });

							connection.query('REPLACE INTO `attendance`(`event`, `user`, `server`, `boss`, `clan`) VALUES ("' + eid + '","' + uid + '","' + server + '","' + bid + '","' + clan + '")', async function (error, results) {
								if (error) throw error;

								await interaction.editReply({ content: 'Посещение «' + bname + '» успешно зафиксировано.', ephemeral: true });
							});
						});
					} else if (interaction.component.style == "DANGER") {
						connection.query('DELETE FROM `attendance` WHERE `event` = "' + eid + '" AND `user` = (SELECT `id` FROM `users` WHERE `dsk_id` = "' + user + '" LIMIT 1)', async function (error, results) {
							if (error) throw error;

							await interaction.editReply({ content: 'Посещение «' + bname + '» успешно отменено.', ephemeral: true });
						});
					}
				} else {
					await interaction.editReply({ content: 'Это событие уже закрыто! Прошло более двух часов с момента запуска.', ephemeral: true });
				}
			});
		} else if (type == "l") {
			connection.query('SELECT `loot`.`id`, `items`.`name` FROM `loot` JOIN `items` ON `items`.`id` = `loot`.`item` WHERE md5(`loot`.`id`) = "' + id + '" AND `status` = 0 LIMIT 1', async function (error, results) {
				if (error) throw error;

				if (results.length !== 0) {
					var lid = results[0].id;
					var lname = results[0].name;

					connection.query('SELECT `users`.`id`, `users`.`clan`, `users`.`party`, `users`.`server`, `users`.`dkp`, IF(`users`.`dkp` >= 1000, 1, 0) AS pointlimit, IF((SELECT COUNT(*) FROM `applicants` JOIN `loot` ON `loot`.`id` = `applicants`.`loot` WHERE(ABS(TIMESTAMPDIFF(MINUTE, NOW(), `loot`.`time`)-2) BETWEEN 0 AND 720) AND `applicants`.`user` = `users`.`id` AND `status` <> 1) < 3, 1, 0) AS applylimit FROM `users` WHERE `users`.`dsk_id` = "' + user + '" LIMIT 1', async function (error, results) {
						if (error) throw error;

						var uid = results[0].id;
						var pid = results[0].party;
						var clan = results[0].clan;
						var server = results[0].server;
						var pointlimit = results[0].pointlimit;
						var applylimit = results[0].applylimit;

						if (!Boolean(clan))
							await interaction.editReply({ content: 'Отсутствует критичная для выполнения запроса роль: Клан', ephemeral: true });

						if (!Boolean(server))
							await interaction.editReply({ content: 'Отсутствует критичная для выполнения запроса роль: Сервер', ephemeral: true });

						if (interaction.component.style == "SUCCESS") {
							if (pointlimit == 0) {
								await interaction.editReply({ content: 'Для того чтобы претендовать на эту вещь у вас должно быть больше 1000 pts.', ephemeral: true });
							} else if (applylimit == 0) {
								await interaction.editReply({ content: 'Вы уже претендуете на 3 вещи, пожалуйста подождите окончания торгов или откажитесь от одной из них.', ephemeral: true });
							} else {
								connection.query('REPLACE INTO `applicants`(`loot`, `user`, `server`, `clan`) VALUES ("' + lid + '","' + uid + '","' + server + '","' + clan + '")', async function (error, results) {
									if (error) throw error;

									await interaction.editReply({ content: 'Вы стали одним из претендентов на «' + lname + '».', ephemeral: true });
								});
							}
						} else if (interaction.component.style == "DANGER") {
							connection.query('DELETE FROM `applicants` WHERE `loot` = "' + lid + '" AND `user` = (SELECT `id` FROM `users` WHERE `dsk_id` = "' + user + '" LIMIT 1)', async function (error, results) {
								if (error) throw error;

								await interaction.editReply({ content: 'Вы больше не претендуете на «' + lname + '».', ephemeral: true });
							});
						}
					});
				} else {
					await interaction.editReply({ content: 'Этот предмет уже нашел своего счастливого владельца.', ephemeral: true });
				}
			});
		} else {
			await interaction.editReply({ content: 'Произошла критическая ошибка.', ephemeral: true });
		}
	}
});

function manageRoles(guild, req, res) {
	guild.members.fetch(req.query.dsk_id).then(async member => {
		Object.values(req.query.roles).forEach(async (role) => {
			if (member.roles.cache.find(r => r.id === role)) {
				try {
					await member.roles.remove(guild.roles.cache.get(role));
				} catch (error) {
					return;
				}
			}
		});

		if (req.query.classes_ds) {
			await member.roles.add(guild.roles.cache.get(req.query.classes_ds));
		}

		if (req.query.servers_ds) {
			await member.roles.add(guild.roles.cache.get(req.query.servers_ds));
		}

		if (req.query.user_ds) {
			await member.roles.add(guild.roles.cache.get(req.query.user_ds));
		}

		if (req.query.user_reg_ds) {
			await member.roles.add(guild.roles.cache.get(req.query.user_reg_ds));
		}

		if (req.query.user_reg_ds) {
			await member.roles.add(guild.roles.cache.get(req.query.user_reg_ds));
		}

		if (guild.ownerId != member.user.id) {
			await member.setNickname(req.query.name);
		}

		res.send("Discord data was changed successfully");
	});
}

function sendEmbed(guild, req, res) {
	if (req.query.components)
		guild.channels.cache.get(req.query.channel).send({ embeds: [JSON.parse(req.query.embeds)], components: [JSON.parse(req.query.components)] });
	else
		guild.channels.cache.get(req.query.channel).send({ content: req.query.content, embeds: [JSON.parse(req.query.embeds)] });

	res.send("Discord data was changed successfully");
}

ds.login(config.dstoken);