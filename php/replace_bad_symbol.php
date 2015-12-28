<?php
include_once './db_connect.php';
//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

if (isset($_FILES['file']['name']) && $_FILES['file']['name']!='') {
    //очищаем базу данных
    include './db_clear.php';
    //читаем файл
    $txt =  file_get_contents($_FILES['file']['tmp_name']);

    $txt = explode('"',$txt);
    //замена (->[, )->], /->|
    for ($i=1;$i<count($txt);$i += 2) {
        $txt[$i] = str_replace('(', '[', $txt[$i]);
        $txt[$i] = str_replace(')', ']', $txt[$i]);
        $txt[$i] = str_replace('/', '|', $txt[$i]);
    }
    $txt = implode('"',$txt);
    
    header("Content-Disposition: attachment; filename=".$_FILES['file']['name']);
    header("Content-Type: application/octet-stream");
    echo $txt;


}
else echo "Не могу прочитать файл *.txt!";

//закрываем соединение с базой
mysql_close($dbh);

?>