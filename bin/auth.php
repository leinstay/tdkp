<?php

/* Авторизация */
define('OAUTH2_CLIENT_ID', $config['sid']);
define('OAUTH2_GUILD_ID', $config['gid']);
define('OAUTH2_CLIENT_SECRET', $config['secret']);

$authorizeURL = 'https://discord.com/api/oauth2/authorize';
$tokenURL = 'https://discord.com/api/oauth2/token';
$apiURLBase = 'https://discord.com/api/users/@me';
$apiURLBase = 'https://discord.com/api/users/@me';
$guildURL = 'https://discord.com/api/users/@me/guilds/' . OAUTH2_GUILD_ID . '/member';
$guildRolesURL = 'https://discord.com/api/guilds/' . OAUTH2_GUILD_ID . '/roles';
$revokeURL = 'https://discord.com/api/oauth2/token/revoke';

function login($sid = null)
{
	global $connection, $guildURL;

	if (empty($sid)) {
		$user = json_decode(json_encode(discordApiRequest($guildURL)), true);
		$roles = $user['roles'];

		if ($user['message']) {
			loginError("Ошибка логина: Вы не числитесь в дискорде Альянса Эрика, пожалуйста перейдите по ссылке: https://discord.gg/JBFdH4RfTA (" . $user['message'] . ").");
		}

		$user = array('dsk_id' => $user['user']['id'], 'name' => $user['nick'] ? $user['nick'] : $user['user']['username'], 'avatar' => $user['user']['avatar'] ? 'https://cdn.discordapp.com/avatars/' . $user['user']['id'] . '/' . $user['user']['avatar'] . '.png?size=2048' : null);

		$admin_roles = getRoles('roles');
		$class_roles = getRoles('classes');
		$server_roles = getRoles('servers');

		$logindata = getLoginData($user['dsk_id']);
		$user['party'] = $logindata[0];
		$user['clan'] = $logindata[1];

		$authorized = 0;

		$user['class'] = null;
		$user['server'] = null;
		$user['adm'] = 0;
		$user['md'] = 0;
		$user['dpl'] = 0;

		foreach ($roles as $id) {
			if (isset($admin_roles[$id]['name']) && $admin_roles[$id]['name'] == 'Авторизован') $authorized = 1;
			if (isset($admin_roles[$id]['name']) && $admin_roles[$id]['name'] == 'Член Совета') $user['adm'] = 1;
			if (isset($admin_roles[$id]['name']) && $admin_roles[$id]['name'] == 'Модератор') $user['md'] = 1;
			if (isset($admin_roles[$id]['name']) && $admin_roles[$id]['name'] == 'Офицер') $user['md'] = 1;
			if (isset($admin_roles[$id]['name']) && $admin_roles[$id]['name'] == 'Дипломат') $user['dpl'] = 1;

			if (isset($class_roles[$id]['id'])) $user['class'] = $class_roles[$id]['id'];
			if (isset($server_roles[$id]['id'])) $user['server'] = $server_roles[$id]['id'];
		}

		if ($authorized === 0) {
			loginError("Ошибка логина: Вы не являетесь авторизованным учасником дискорда альянса Эрика. Обратитесь к любому офицеру в разделе [#приемная] и попросите выдать вам роль [@авторизован].");
		}

		$driver = $connection->prepare("SELECT `dsk_id` FROM `drivers` WHERE `adsk_id` = ? LIMIT 1");
		$driver->execute(array($user['dsk_id']));
		$driver = $driver->fetchColumn();

		if (empty($driver)) {
			$connection->prepare("INSERT INTO `users`(`dsk_id`, `name`, `avatar`, `server`, `clan`, `class`, `party`, `adm`, `md`, `dpl`) 
				VALUES (:dsk_id, :name, :avatar, :server, :clan, :class, :party, :adm, :md, :dpl)
				ON DUPLICATE KEY UPDATE `name`=:name, `avatar`=:avatar, `server`=:server, `clan`=:clan, `class`=:class,
				`party`=:party, `adm`=:adm, `md`=:md, `dpl`=:dpl")->execute(array_merge($user, $user));
		} else {
			$user['dsk_id'] = $driver;
		}

		$user['id'] = $connection->query("SELECT `id` FROM `users` WHERE `dsk_id` =" . $user['dsk_id'] . " LIMIT 1")->fetchColumn();

		$user = getUser($user['id']);
	} else {
		$user = getUser($sid);
	}

	if (!$user['clan'] || !$user['class'] || !$user['server']) {
		$_SESSION['registration'] = true;
		$_SESSION['dsk_id'] = $user['dsk_id'];
		loginError("Ошибка логина: Отсутствует одна из критических ролей в Discord, пожалуйста заполните форму ниже.<br>Если у вас уже есть КП выберите её в поле «Существующее КП», если вы лидер новой КП введите её название в поле «Новое КП», иначе оставьте оба поля пустыми.");
	}

	return $user;
}

/* Реавторизация через куки */
if ($_COOKIE['access_token'] && $_COOKIE['user'] && !$_SESSION['error']) {
	$_SESSION['access_token'] = $_COOKIE['access_token'];
	if (is_int((int)base64_decode($_COOKIE['user'])))
		$_SESSION['user'] = login(base64_decode($_COOKIE['user']));
}

/* Флоу авторизации через Discord */
if ($_GET['p'] == 'gateway' && $_GET['a'] == 'login') {
	if ($_GET['code']) {
		$token = discordApiRequest($tokenURL, array(
			"grant_type" => "authorization_code",
			'client_id' => OAUTH2_CLIENT_ID,
			'client_secret' => OAUTH2_CLIENT_SECRET,
			'redirect_uri' => 'https://justdkp.com/?p=gateway&a=login',
			'code' => $_GET['code']
		));

		if ($token->error) {
			loginError("Ошибка логина: Общая ошибка Discord, обратитесь к админам (" . $token->error_description . ").");
		} else {
			$_SESSION['access_token'] = $token->access_token;
			$_SESSION['user'] = login();

			setcookie("access_token", $_SESSION['access_token'], time() + 150000);
			setcookie("user", base64_encode($_SESSION['user']['id']), time() + 150000);
		}
	} else {
		$params = array(
			'client_id' => OAUTH2_CLIENT_ID,
			'redirect_uri' => 'https://justdkp.com/?p=gateway&a=login',
			'response_type' => 'code',
			'scope' => 'identify guilds guilds.members.read'
		);

		header('Location: https://discord.com/api/oauth2/authorize' . '?' . http_build_query($params));
		die();
	}
}

/* Логаут / Перерегистрация */
if ($_GET['a'] == 'logout' || $_GET['a'] == 'register') {
	if ($_GET['a'] == 'register') {
		$connection->prepare("DELETE FROM `applicants` WHERE `user` = ?")->execute(array($_SESSION['user']['id']));
		$data = $connection->prepare("UPDATE `users` SET `server` = null, `clan` = null, `class` = null, `party` = null WHERE `id` =  ?");
		$data->execute(array($_SESSION['user']['id']));
	}

	discordApiLogout($revokeURL, array(
		'token' => $_SESSION['access_token'],
		'token_type_hint' => 'access_token',
		'client_id' => OAUTH2_CLIENT_ID,
		'client_secret' => OAUTH2_CLIENT_SECRET,
	));

	unset($_SESSION['access_token']);
	unset($_SESSION['user']);

	unset($_COOKIE['access_token']);
	unset($_COOKIE['user']);

	setcookie('access_token', '', time() - 3600, '/');
	setcookie('user', '', time() - 3600, '/');

	header("Refresh:0; url=/?p=gateway");
	die();
}

/* Рутинг */
if (empty($_GET['p']) || $_GET['p'] == "gateway" && $_SESSION['user']) {
	header("Refresh:0; url=/?p=profile");
	die();
} else if ($_GET['p'] != "gateway" && $_GET['p'] != "dkp" && $_GET['p'] != "rules" && $_GET['p'] != "about" && !$_SESSION['user']) {
	header("Refresh:0; url=/?p=gateway");
	die();
} else {
	if (file_exists("pages/" . $_GET['p'] . ".php")) {
		$page = $_GET['p'];
	} else {
		$page = "default";
	}
}
