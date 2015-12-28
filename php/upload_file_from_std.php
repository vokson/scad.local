<?php
/**
 * Загружаем *.STD файл
 */

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

if (isset($_FILES['file']['name']) && $_FILES['file']['name']!='') {
    //очищаем базу данных
    include './db_clear.php';
    //читаем файл
    $txt = file($_FILES['file']['tmp_name']);
    //убираем первые 17 строк и последние 5
    array_splice($txt,0,17);
    array_splice($txt,-5,5);
    
    $txt = implode("", $txt);

    
    $f = fopen('test.txt','w');
    fwrite($f,$txt);
    fclose($f);

    $txt = explode('*',$txt);
    for ($i=0;$i<count($txt);$i += 2)
        $document[trim($txt[$i])] = $txt[$i+1];

    //разбираем узлы
    if (isset($document['JOINT COORDINATES'])) {
        $nodes = new TNode();
        $nodes->get_from_staad_std($document['JOINT COORDINATES']);
        echo '- Узлы;<br/>';
     }

     //разбираем элементы
    if (isset($document['MEMBER INCIDENCE'])) {
        $members = new TMember();
        $members->get_from_staad_std($document['MEMBER INCIDENCE']);
        echo '- Элементы;<br/>';
     }

}
//если имя файла пустое
else echo "Не могу прочитать файл *.std!";

//закрываем соединение с базой
mysql_close($dbh);
?>