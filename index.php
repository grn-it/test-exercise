<?php

require_once './advertisement.php';

$id = $_GET['id'];
$from = $_GET['from'];

$advertisement = new Advertisement(new Rub(), new ArrayFormatter());
$advertisementData = [];

switch ($from) {
    case 'Mysql': {
        $advertisementData = $advertisement->getAdRecord($id);
        break;
    }
    case 'Daemon': {
        $advertisementData = $advertisement->get_daemon_ad_info($id);
        break;
    }
}

if ($advertisementData) {
    new View($advertisementData, $advertisement->getCurrencyName());
}

