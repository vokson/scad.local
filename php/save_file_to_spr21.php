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
$groups_for_steel = new TMemberGroupSteel21();
$s = $groups_for_steel->set_to_scad_spr();
$body_hex = bin2hex($s);
$sql = "UPDATE " . document_bin . " SET body = 0x$body_hex WHERE number = " . D_member_group_for_steel;
mysql_query($sql);

//обновляем комбинации
//$combinations = new TCombination();
//$s = $combinations->set_to_scad_spr();
//$body_hex = bin2hex($s);
//$sql = "UPDATE " . document_bin . " SET body = 0x$body_hex WHERE id = " . D_combinations;
//mysql_query($sql);

define('START_OFFSET', 8);

//собираем документ
$today = date("d.m.Y");
header("Content-Disposition: attachment; filename=$today.spr");
header("Content-Type: application/octet-stream");

$sql = "SELECT * FROM " . document_bin . " ORDER BY id ASC";
mysql_query($sql);
switch (mysql_errno()) {
    case 1146: echo "<b>Table " . document_bin . " doesn't exist. Please create DB.</b><br>";
        break;
    default:
        if (mysql_errno() > 0) {
            echo mysql_errno() . '  ' . mysql_error() . '<br>';
        }
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $doc = new TDoc($row['number'], 0, 0);
                $doc->body = $row['body'];
                $doc->appendix = $row['appendix'];
                $document[] = $doc;
            }
        }
        //закрываем соединение с базой
        mysql_close($dbh);

        // Обновляем данные в документах
        $offset = START_OFFSET + 4;
        for ($i = 0; $i < count($document); $i++) {
            $document[$i]->byte_offset = $offset;
            $document[$i]->byte_count = strlen($document[$i]->body);
            $offset += $document[$i]->byte_count + strlen($document[$i]->appendix);
        }

        //    foreach ($document as $doc) {
//        echo "$doc->number - $doc->byte_offset - $doc->byte_count<br/>";
//    //    echo $doc->body.'<br/>';
//    }
        //пишем файл *.SPR
        $f = fopen("./TEMP.SPR", "wb+");
        fwrite($f, '*Schema*');

        //offset описания документов в конце файла
        fwrite($f, pack('V', $offset));

        // Пишем документы
        foreach ($document as $doc) {
            fwrite($f, $doc->body);
            if (!is_null($doc->appendix)) {
                fwrite($f, $doc->appendix);
            }
        }

        // Удаляем 0 документ
        unset($document[0]);

        // Сортируем массив по number
        usort($document, 'compareDocByNumber');

        //Пишем offset и кол-во байт документов в конце файла
        foreach ($document as $doc) {
            fwrite($f, pack('v', $doc->number));
            // Т.к. 32-битный PHP не поддерживает pack('P', ..)
            // Пакуем число в int(4) и пишем 4 пустых байта
            fwrite($f, pack('V', $doc->byte_offset));
            fwrite($f, "\x00\x00\x00\x00");
            fwrite($f, pack('V', $doc->byte_count));
            fwrite($f, "\x00\x00\x00\x00");
        }

        //00 00 - в конце
        fwrite($f, "\x00\x00");

        //закрываем файл
        fclose($f);

        //читаем файл *.SPR
        $f = fopen("./TEMP.SPR", "rb");
        echo fread($f, filesize("./TEMP.SPR"));
        fclose($f);
}


