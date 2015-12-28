<?php
//Максимальное время работы скрипта 1 час
set_time_limit(3600);


//записывает одну группу в таблицу WORD
//list - номера элементов через пробел
//column_name - массив нужных столбцов
//курсор д.б. установлен на нужное место в таблице
//name - имя группы
//row - номер строки в таблице, на которой сейчас курсор
//строки считаются с 1
function group_to_word (&$word, $base_name, $name, $list, $column_name, &$row_number) {
  if ($list && $list != '') {
    //выделяем строку row_number левую ячейку
    $word->Selection->Tables(1)->Cell($row_number, 1)->Select();
    
    //заменяем в list пробелы на запятые
    $list = str_replace(' ', ',', $list);
    $sql = "SELECT * FROM ".$base_name." WHERE element IN (".$list.")";
    //echo $sql.'<br/>';
    mysql_query($sql);
    switch (mysql_errno()) {
     case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
     default:
        if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
           //вставляем строку
           $word->Selection->InsertRowsBelow(1);
           //выделяем и объединяем строку с номером row
            $myTable = $word->ActiveDocument->Tables(1);
            $rangeStart = $myTable->Cell($row_number,1);
            $myRangeStart = $rangeStart->Range->Start();
            $rangeEnd = $myTable->Cell($row_number,count($column_name));
            $myRangeEnd = $rangeEnd->Range->End();
            $myRange = $word->ActiveDocument->Range($myRangeStart,$myRangeEnd);
            $myRange->Cells->Merge();
            
            //текст жирным
            $word->Selection->Font->Bold = true;
            //тескт по центру
            $word->Selection->ParagraphFormat->Alignment = 1;
            //пишем название группы
            $word->Selection->TypeText(iconv('utf-8','windows-1251', $name));

            $row_number++;

            //спускаемся на строку ниже в левую ячейку
            $word->Selection->Tables(1)->Cell($row_number, 1)->Select();

           while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
               foreach ($column_name as $value) {
                    //пишем значение и сдвиг вправо
                    $word->Selection->TypeText(iconv('utf-8','windows-1251', $row[$value]));
                    $word->Selection->MoveRight();
               }
            //Вставляем еще одну строку и сдвигаемся на самую левую ячейку
            $word->Selection->InsertRowsBelow(1);
            $word->Selection->MoveLeft();
            $row_number++;
           }
        }
    }
  }
}


//перебираем базу  base_name, выбираем из нее
//name и list из строк
//word - COM объект, в котором работаем
function prepare_groups_to_word(&$word, $base_name,$id_list,$column_name,&$row_number) {
    if ($id_list) {
        $sql = "SELECT name,list FROM ".$base_name." WHERE id IN (".implode(',',$id_list).')';
//        echo $sql.'<br/>';
            mysql_query($sql);
            switch (mysql_errno()) {
             case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
             default:
                if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0)
                   while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                      //пишем группу в word, выбираем нужна унификация или нет
                      if ($_POST['unification'] == 0)
                          group_to_word ($word, RSU, $row['name'], $row['list'], $column_name, $row_number);
                      else
                          group_to_word ($word, RSU_UNIF, $row['name'], $row['list'], $column_name, $row_number);
            }
    }
}

//подключаемся к базе
include '../db_connect.php';
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//массив нужных столбцов
$column_name = array('element','N','Mk','My','Qz','Mz','Qy','formula');

// запускаем Word
$word = new COM("word.application") or die("Ошибка запуска Word");
//echo "Word запушен, версия {$word->Version}<br>";

// поверх всех окон
$word->Visible = 1;
// просто открываем существующий
$word->Documents->Open(realpath("./template.doc"));
$word->Selection->Font->Name = 'Times New Roman';

//производим замену [begin]
//не знаю зачем, но без этого выделение ячейки в таблице не работает
//может сначала надо в нее войти
$word->Selection->Find->Execute('[begin]',0,0,0,0,0,1,2,0,"",1,0,0,0,0);
$word->Selection->TypeText('');

//чтение свойств
$row_number = 3; // начинаем с 3-ей строки таблицы
prepare_groups_to_word($word, member_group, $_POST['mas_group'],$column_name, $row_number);
prepare_groups_to_word($word, member_group_for_steel, $_POST['mas_group_for_steel'],$column_name, $row_number);

//сохраняем файл
$word->ChangeFileOpenDirectory(realpath('./'));
$word->ActiveDocument->SaveAs('result.doc');

//// закрываем без лишних диалогов
//$word->Documents[1]->Close(0);
//// выход из Word
//$word->Quit();

// очистка экземпляра COM
$word = NULL;

echo 'Word document was created.';

//закрываем соединение с базой
mysql_close($dbh);
?>