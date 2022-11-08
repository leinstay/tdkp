<?php

include_once('bin/config.php');
include_once('bin/api.php');
include_once('bin/auth.php');

if (isset($_SESSION['user']['id'])) {
    $_SESSION['user'] = getUser($_SESSION['user']['id']);
}

include_once("templates/header.php");
include_once("templates/sidebar.php");
include_once("templates/topbar.php");

if ($_SESSION['message']) {
    $info = $templates['message'];
    echo str_replace('{{info}}', $_SESSION['message'], $info);
    unset($_SESSION['message']);
}
if ($_SESSION['alert']) {
    $info = $templates['alert'];
    echo str_replace('{{info}}', $_SESSION['alert'], $info);
    unset($_SESSION['alert']);
}

include_once("pages/$page.php");

include_once("templates/modals.php");
include_once("templates/footer.php");
