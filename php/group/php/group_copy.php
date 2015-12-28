<?php
//копирует строки из одной таблицы в другую
function copy_list($base_output,$base_input,$id_list) {
    if ($id_list) {
        $sql = "SELECT name,list FROM ".$base_output." WHERE id IN (".implode(',',$id_list).')';
//        echo $sql.'<br/>';
            mysql_query($sql);
            switch (mysql_errno()) {
             case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
             default:
                if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0)
                   while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                       mysql_query ('INSERT INTO '.$base_input." (name,list) VALUES ('".$row['name']."','".$row['list']."')");
            }
        echo 'Copy complete.';
    }
}

include_once '../../db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//print_r($_POST);
//копирование
if ($_POST['copy_direction'] == 'LEFT')
    copy_list(member_group_for_steel, member_group, $_POST['list']);

if ($_POST['copy_direction'] == 'RIGHT')
    copy_list(member_group, member_group_for_steel, $_POST['list']);

//закрываем соединение с базой
 mysql_close($dbh);
?>