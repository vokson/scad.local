<?php
/**
 * Загружаем текстовый scad файл без повторителей
 *
 */
//Максимальное время работы скрипта 1 час
set_time_limit(3600);
//Выделяем память для работы скрипта
ini_set('memory_limit', '512M');

//функции автозагрузки классов
function convert_class_to_filename($class) {
  return './classes/class_'.str_replace('_', '/', $class).'.php';
}

function __autoload($class) {
  @include_once(convert_class_to_filename($class));
}

function timeMeasure() {
    list($msec, $sec) = explode(chr(32), microtime());
    return ($sec+$msec);
}

//очищаем базу данных
include './db_clear.php';
include './db_create.php';
    
include_once './db_connect.php';
//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");



if (isset($_FILES['file']['name']) && $_FILES['file']['name']!='') {
    
    //читаем файл
    $txt = iconv('CP1251', 'UTF-8', file_get_contents($_FILES['file']['tmp_name']));
    
    echo 'Найдено '.preg_match_all('|(?<=\()[^\(\)]*(?=\))|', $txt, $d,PREG_PATTERN_ORDER).' документов в файле.<br/>';
    for ($i=0;$i<count($d[0]);$i++) {
        $doc = explode('/',$d[0][$i]);
        $doc_number = $doc[0];
        array_splice($doc,0,1);
        $doc_content = implode('/',$doc);
        $document[$doc_number] = $doc_content;
    }
    echo 'Загружены документы:<br/>';

//    print_r($document);
    //разбираем группы элементов
    $TIMESTART = timeMeasure();
    if (isset($document[D_member_group])) {
        $member_groups = new TMemberGroup();
        $member_groups->get_from_scad_txt($document[D_member_group]);
        echo D_member_group.' - группы элементов;<br/>';
     }
     echo round(timeMeasure()-$TIMESTART, 6).' сек<br/>';

     $TIMESTART = timeMeasure();
     //разбираем группы для подбора стали
    if (isset($document[D_member_group_for_steel])) {
        $member_groups_for_steel = new TMemberGroupSteel();
        $member_groups_for_steel->get_from_scad_txt($document[D_member_group_for_steel]);
        echo D_member_group_for_steel.' - группы элементов для подбора;<br/>';
     }
      echo round(timeMeasure()-$TIMESTART, 6).' сек<br/>';

     $TIMESTART = timeMeasure();
     //разбираем узлы
    if (isset($document[D_node])) {
        //замена переносов
        $document[D_node] = str_replace("\r\n",' ',$document[D_node]);
        $document[D_node] = str_replace("\n",' ',$document[D_node]);
        //замена двойных пробелов
        $document[D_node] = preg_replace("|\s{2,}|", ' ', $document[D_node]);
        
        $nodes = new TNode();
        $nodes->get_from_scad_txt($document[D_node]);
        echo D_node.' - узлы;<br/>';
     }
      echo round(timeMeasure()-$TIMESTART, 6).' сек<br/>';

     $TIMESTART = timeMeasure();
     //разбираем элементы
    if (isset($document[D_member])) {
        //замена переносов
        $document[D_member] = str_replace("\r\n",' ',$document[D_member]);
        $document[D_member] = str_replace("\n",' ',$document[D_member]);
        //замена двойных пробелов
        $document[D_member] = preg_replace("|\s{2,}|", ' ', $document[D_member]);
        
        $members = new TMember();
        $members->get_from_scad_txt($document[D_member]);
        echo D_member.' - элементы;<br/>';
     }
      echo round(timeMeasure()-$TIMESTART, 6).' сек<br/>';

    //пишем таблицу DOCUMENT
    mysql_query("TRUNCATE TABLE ".document);
     foreach ($document as $id=>$text)
         mysql_query ('INSERT INTO '.document." (id,text) VALUES ('$id','$text')");
     
}
//если имя файла пустое
else echo "Не могу прочитать файл *.txt!";

//закрываем соединение с базой
mysql_close($dbh);
?>