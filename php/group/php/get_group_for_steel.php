<?php
//Возвращает список групп элементов
include_once '../../db_connect.php';
//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

$sql = "SELECT * FROM ".member_group_for_steel;
//." ORDER BY id";
    mysql_query($sql);
    switch (mysql_errno()) {
     case 1146: echo "<b>Table ".member_group_for_steel." doesn't exist. Please create DB.</b><br>";break;
     default:
        if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
           while($row = mysql_fetch_array($result, MYSQL_ASSOC))
               echo '<div class="group_for_steel" value="'.$row['id'].'">'.$row['id'].') '.$row['name']."</div>\r\n";
    }

//закрываем соединение с базой
 mysql_close($dbh);
?>