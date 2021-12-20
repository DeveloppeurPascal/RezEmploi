<?php
if ((!defined("REZEMPLOIKEY")) || (REZEMPLOIKEY != "3f5gd4ng2h5j4gh24,gh2j54fd2g54fg2h45")) {
    header("location: index.php");
    exit;
}

function generer_identifiant($taille = 10)
{
    $id = "";
    for ($j = 0; $j < $taille / 5; $j++) {
        $num = mt_rand(0, 99999);
        for ($i = 0; $i < 5; $i++) {
            $id = ($num % 10) . $id;
            $num = floor($num / 10);
        }
    }
    return (substr($id, 0, $taille));
}

function addlog($msg)
{
    if (_DEBUG) {
        $f = fopen(__DIR__ . "/../temp/log-" . date("Ymd") . ".txt", "a");
        fwrite($f, date("YmdHis") . " " . $msg);
        fwrite($f, "------------------------------");
        fclose($f);
    }
}