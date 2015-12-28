<?php

//Максимальное время работы скрипта 1 час
set_time_limit(30);
//Выделяем память для работы скрипта
ini_set('memory_limit', '16M');

function timeMeasure() {
    list($msec, $sec) = explode(chr(32), microtime());
    return ($sec + $msec);
}

include_once '../../db_connect.php';
//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
mysql_query("SET NAMES utf8");

mysql_query("TRUNCATE TABLE " . combinations);

// UPLOAD GROUPS
$file = file_get_contents('Model.json');
$array = json_decode($file, TRUE);

// Function to compare array's elements by name
function cmp($a, $b)
{
    return strcmp($a['name'], $b['name']);
}

$groupArray = $array['SteelMemberCheckGroups'];
usort($groupArray, 'cmp');

// WRITE GROUPS TO DATABASE
mysql_query("TRUNCATE TABLE " . member_group_for_steel);

foreach ( $groupArray as $group) {

    $name = $group['name'];
    $steel = $group['steel'];
    $Ry = $group['Ry'];
    $FC = $group['FC'];
    $mu_XZ = $group['muXZ'];
    $mu_XY = $group['muXY'];
    $gamma_c = $group['gammaC'];
    $list = $group['list'];

    mysql_query("INSERT IGNORE INTO " . member_group_for_steel . " SET
                      steel = '$steel',
                      Ry = '$Ry',
                      gamma_c = '$gamma_c',
                      FC = '$FC',
                      group_type = '0',
                      mu_XZ = '$mu_XZ',
                      mu_XY = '$mu_XY',
                      name = '$name',
                      list = '" . implode(' ', $list) . "'"
    );

    echo "$name => $steel($Ry) |$gamma_c| $mu_XZ, $mu_XY - $FC *" . implode(', ', $list) . "*<br/>";
}

echo count($array['SteelMemberCheckGroups']) . ' groups have been created.<br/>';

//закрываем соединение с базой
mysql_close($dbh);
