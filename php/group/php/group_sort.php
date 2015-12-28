<?php
//формируем список имен свойств
function get_prop_list ($base_name,&$mas) {
    $result = mysql_query('EXPLAIN '.$base_name);
    if (mysql_num_rows($result) > 0)
       while($row = mysql_fetch_array($result, MYSQL_ASSOC))
            $mas[] = $row['Field'];
}

//копирует строки из одной таблицы в другую
function sort_base($base_name,$direction) {
    //создаем еще одну таблицу
    $sql = "CREATE TABLE tmp SELECT * FROM $base_name ORDER BY CAST(name as UNSIGNED) $direction";
    mysql_query($sql);
    //очищаем исходную
    $sql = "TRUNCATE TABLE $base_name";
    mysql_query($sql);
    //формируем список имен свойств
    $prop_list = array();
    get_prop_list($base_name, $prop_list);
    //удаляем из массива id
    array_splice($prop_list, 0, 1);
    //копируем из временной в исходную все кроме id
    $sql = "INSERT INTO $base_name (".implode(',',$prop_list).") SELECT ".implode(',',$prop_list)." FROM tmp";
    mysql_query($sql);
    //удаляем временную таблицу
    $sql = "DROP TABLE tmp";
    mysql_query($sql);
}

include_once '../../db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//print_r($_POST);
//сортировка
sort_base(member_group, $_POST['direction']);
sort_base(member_group_for_steel, $_POST['direction']);

echo 'Сортировка завершена.';
//закрываем соединение с базой
 mysql_close($dbh);
?>