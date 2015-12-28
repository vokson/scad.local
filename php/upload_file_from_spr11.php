<?php

/**
 * Загружаем SCAD11 *.SPR файл
 *
 */
//Максимальное время работы скрипта 1 час
set_time_limit(300);
//Выделяем память для работы скрипта
ini_set('memory_limit', '32M');

//функции автозагрузки классов
function convert_class_to_filename($class) {
    return './classes/class_' . str_replace('_', '/', $class) . '.php';
}

function __autoload($class) {
    @include_once(convert_class_to_filename($class));
}

function timeMeasure() {
    list($msec, $sec) = explode(chr(32), microtime());
    return ($sec + $msec);
}

//очищаем базу данных
include './db_clear.php';
include './db_create.php';

include_once './func.php';
include_once './db_connect.php';
//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
mysql_query("SET NAMES utf8");

define('START_OFFSET', 8);
define('MAX_DOC', 100);

if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {

    //читаем файл *.SPR
    $f = fopen($_FILES['file']['tmp_name'], "rb");

    //Формат начала файла
    //*schema*_int32 - offset описания документов
    fseek($f, 8);
    // (int) 4 - offset описания документов
    $docDescriptionOffset = bytes2un_int(fread($f, 4));
    // Минимальный offset
    $minDocOffset = $docDescriptionOffset;

    // Читаем массив адресов документов в конце файла
    // Переходим на первый документ
    fseek($f, $docDescriptionOffset);

    $document = array();
    // Добавляем пустой элемент для документа No.0
    $document[0] = 0;

    $isFinalDoc = FALSE;
    while ($isFinalDoc === FALSE) {
        // читаем номер документа
        $number = unpackInt_2(fread($f, 2));
        // если номер положителен, читаем offset и count
        if ($number > 0) {
            $offset = unpackInt_4(fread($f, 4));
            $count = unpackInt_4(fread($f, 4));
            $doc = new TDoc($number, $offset, $count);
            $document[$number] = $doc;

//            echo "$doc->number - $doc->byte_offset - $doc->byte_count<br/>";

            if ($doc->byte_offset < $minDocOffset) {
                $minDocOffset = $doc->byte_offset;
            }
        } else {
            $isFinalDoc = TRUE;
        }
    }

    // Добавляем документ No.0
    // Байты между указателем на адрес описания документов и первым записанным документом
    $document[0] = new TDoc(0, START_OFFSET + 4, $minDocOffset - START_OFFSET - 4);

    // Читаем документы в массив
    foreach ($document as &$object) {
        // Помещаем указатель в начало документа
        fseek($f, $object->byte_offset);
        // Читаем документ
        $object->body = fread($f, $object->byte_count);
    }
    
    //Переписываем тело документа 3
    //Считая, что после док-та 3 идет док-т 31
    fseek($f, $document[3]->byte_offset);
    $document[3]->body = fread($f, $document[31]->byte_offset - $document[3]->byte_offset);

    //закрываем файл
    fclose($f);

    echo 'Найдено ' . count($document) . ' документов в файле.<br/>';


    echo 'Загружены документы:<br/>';

//    print_r($document);
    $TIMESTART = timeMeasure();
    //разбираем группы для подбора стали
    if (isset($document[D_member_group_for_steel])) {
        $member_groups_for_steel = new TMemberGroupSteel11();
        $member_groups_for_steel->get_from_scad_spr($document[D_member_group_for_steel]->body);
        echo D_member_group_for_steel . ' - группы элементов для подбора;<br/>';
    }
    //разбираем комбинации
    if (isset($document[D_combinations])) {
        $combinations = new TCombination();
        $combinations->get_from_scad_spr($document[D_combinations]->body);
        echo D_combinations . ' - комбинации;<br/>';
    }

    echo round(timeMeasure() - $TIMESTART, 6) . ' сек<br/>';


    //пишем таблицу DOCUMENT_BIN
    mysql_query("TRUNCATE TABLE " . document_bin);
    for ($i = 0; $i <= MAX_DOC; $i++) {
        if (isset($document[$i])) {
            $doc = $document[$i];
            $body_hex = bin2hex($doc->body);
            $sql = 'INSERT INTO ' . document_bin . " (number, byte_offset, byte_count, body) VALUES ($doc->number, $doc->byte_offset, $doc->byte_count, 0x$body_hex)";
//            echo $sql.'<br/>';
            mysql_query($sql);
            if (mysql_errno() > 0) {
                echo mysql_errno() . '  ' . mysql_error() . '<br/>';
            }

        }
    }
}
//если имя файла пустое
else {
    echo "Не могу прочитать файл *.spr!";
}

//закрываем соединение с базой
mysql_close($dbh);
