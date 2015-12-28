<?php

//функции автозагрузки классов
function convert_class_to_filename($class) {
    return './classes/class_' . str_replace('_', '/', $class) . '.php';
}

function __autoload($class) {
    @include_once(convert_class_to_filename($class));
}

include_once './db_connect.php';
include_once './func.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
mysql_query("SET NAMES utf8");

//обновляем группы для подбора стали
$groups_for_steel = new TMemberGroupSteel11();
$s = $groups_for_steel->set_to_scad_spr();
$body_hex = bin2hex($s);
$sql = "UPDATE " . document_bin . " SET body = 0x$body_hex WHERE number = " . D_member_group_for_steel;
mysql_query($sql);

//обновляем комбинации
$combinations = new TCombination();
$s = $combinations->set_to_scad_spr();
$body_hex = bin2hex($s);
$sql = "UPDATE " . document_bin . " SET body = 0x$body_hex WHERE number = " . D_combinations;
mysql_query($sql);

define('START_OFFSET', 8);
define('MAX_DOC', 100);

//собираем документ
$today = date("d.m.Y");
header("Content-Disposition: attachment; filename=$today.spr");
header("Content-Type: application/octet-stream");

$sql = "SELECT * FROM " . document_bin;
mysql_query($sql);
switch (mysql_errno()) {
    case 1146: echo "<b>Table " . document_bin . " doesn't exist. Please create DB.</b><br>";
        break;
    default:
        if (mysql_errno() > 0)
            echo mysql_errno() . '  ' . mysql_error() . '<br>';
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $doc = new TDoc($row['number'], $row['byte_offset'], $row['byte_count']);
                $doc->body = $row['body'];
                $document[$doc->number] = $doc;
            }
        }

//     print_r($document);  
        //пишем файл *.SPR
        $f = fopen("./TEMP.SPR", "wb+");

        //начинаем расчет offset документов, прибавляем размер нулевого документа
        $document[0]->byte_offset = START_OFFSET + 4;
        $doc_offset = $document[0]->byte_offset + $document[0]->byte_count;

        //$sum = 0;
        for ($i = 1; $i <= MAX_DOC; $i++) {
            if (isset($document[$i])) {
                //прописываем для каждого документа кол-во байт
                if ($i != 3) {
                    $document[$i]->byte_count = strlen($document[$i]->body);
                }
                //прописываем offset
                $document[$i]->byte_offset = $doc_offset;
                //вычисляем offset документов
                $doc_offset += strlen($document[$i]->body);
//            echo $doc_offset.'<br/>';
            }
        }

        //print_r($document);
//    echo "document OFFSET - $doc_offset<br/>";
        //echo "SUM = $sum<br/>";
//
//    foreach ($document as $doc) {
//        echo "$doc->number - $doc->byte_offset - $doc->byte_count<br/>";
//    //    echo $doc->body.'<br/>';
//    }

        fwrite($f, '*schema*');
        //offset документов
        fwrite($f, pack('V', $doc_offset));
        //документ 0
        fwrite($f, $document[0]->body);
        //оставшиеся документы
        for ($i = 1; $i <= MAX_DOC; $i++) {
            if (isset($document[$i])) {
                fseek($f, $document[$i]->byte_offset);
                fwrite($f, $document[$i]->body);
                //пишем offset
                fseek($f, $doc_offset);
                fwrite($f, pack('v', $i));
                fwrite($f, pack('V', $document[$i]->byte_offset));
                fwrite($f, pack('V', $document[$i]->byte_count));
                $doc_offset += 10;
            }
        }
        //00 00 - в конце
        fseek($f, $doc_offset);
        fwrite($f, chr(0) . chr(0));

        //закрываем файл
        fclose($f);

        //читаем файл *.SPR
        $f = fopen("./TEMP.SPR", "rb");
        echo fread($f, filesize("./TEMP.SPR"));
        fclose($f);
}

//закрываем соединение с базой
mysql_close($dbh);
