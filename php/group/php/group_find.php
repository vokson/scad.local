<?php
//копирует строки из одной таблицы в другую
function find($base_name,$reg_exp='') {
    $list = array();
    $sql = 'SELECT id FROM '.$base_name." WHERE name LIKE '%".$reg_exp."%'";
//      echo $sql.'<br/>';
    mysql_query($sql);
    switch (mysql_errno()) {
     case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
     default:
        if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0)
           while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                $list[] = $row['id'];
    }
    return $list;
}

include_once '../../db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

$list_group = $list_group_for_steel = NULL;

//print_r($_POST);
//получаем строку для поиска

$reg_exp = explode(':',$_POST['reg_exp']);
if (count($reg_exp) == 1) $column = 'LR'; else {
    $column = $reg_exp[0];
    array_splice($reg_exp,0,1);
}
//print_r($reg_exp).'<br/>';
//echo 'COLUMN = '.$column.'<br/>';
$reg_exp = implode(':',$reg_exp);
//echo 'REG_EXP = '.$reg_exp.'<br/>';

//ищем id
if ($column == 'L' || $column == 'LR') $list_group = find (member_group, $reg_exp);
if ($column == 'R' || $column == 'LR') $list_group_for_steel = find (member_group_for_steel, $reg_exp);
//ответ в формате JSON
if ($list_group === NULL) $list_group = array();
if ($list_group_for_steel === NULL) $list_group_for_steel = array();
$mas = array('group'=>$list_group,'group_for_steel'=>$list_group_for_steel);
echo json_encode($mas);

//закрываем соединение с базой
 mysql_close($dbh);
?>