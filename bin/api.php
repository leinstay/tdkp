<?php

if ($_GET['p'] == "api" && $_GET['t'] == $config['api']) {
    $data = [];

    if ($_GET['q'] == "boss") {
        if (empty($_GET['s'])) $_GET['s'] = 'E1';

        $data = $connection->prepare("SELECT `name`, `respawn`, `dkp`, `chance`, `last_spawn_{$_GET['s']}` AS last_spawn, `next_spawn_{$_GET['s']}` AS next_spawn FROM `bosses`");
        $data->execute(array());
        $data = $data->fetchAll(PDO::FETCH_ASSOC);
    }

    if (!$data)
        die(json_encode("Error", JSON_UNESCAPED_UNICODE));
    else
        die(json_encode($data, JSON_UNESCAPED_UNICODE));
}
