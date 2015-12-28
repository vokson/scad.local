<?php
include_once  './db_connect.php';

//подключаемся к MySQL
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");

//Удаляем базу даных
mysql_query("DROP DATABASE ".SCAD);

//закрываем соединение с базой
mysql_close($dbh);
?>