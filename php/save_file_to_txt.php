<?php
//функции автозагрузки классов
function convert_class_to_filename($class) {
  return './classes/class_'.str_replace('_', '/', $class).'.php';
}

function __autoload($class) {
  @include_once(convert_class_to_filename($class));
}

include_once './db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");


//обновляем узлы
$nodes = new TNode();
$sql = "UPDATE ".document." SET text = '".$nodes->set_to_scad_txt()."' WHERE id = ".D_node;
mysql_query($sql);

//обновляем элементы
$members = new TMember();
$s = iconv('UTF-8', 'CP1251',$members->set_to_scad_txt());
$sql = "UPDATE ".document. " SET text = '$s' WHERE id = ".D_member;
mysql_query($sql);

//обновляем группы для подбора стали
$groups_for_steel = new TMemberGroupSteel();
$s = iconv('UTF-8', 'CP1251',$groups_for_steel->set_to_scad_txt());
$sql = "UPDATE ".document. " SET text = '$s' WHERE id = ".D_member_group_for_steel;
mysql_query($sql);

//собираем документ
$today = date("d.m.Y");
header("Content-Disposition: attachment; filename=$today.txt");
header("Content-Type: application/octet-stream");

$sql = "SELECT * FROM ".document;
mysql_query($sql);
switch (mysql_errno()) {
 case 1146: echo "<b>Table ".document." doesn't exist. Please create DB.</b><br>";break;
 default:
    if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0)
       while($row = mysql_fetch_array($result, MYSQL_ASSOC))
             echo iconv('UTF-8', 'CP1251','('.$row['id'].'/'.$row['text'].')'."\r\n");
}

//закрываем соединение с базой
 mysql_close($dbh);
?>