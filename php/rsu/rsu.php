<?php
//Максимальное время работы скрипта 1 час
set_time_limit(3600);

//функции автозагрузки классов
function convert_class_to_filename($class) {
  return 'classes/class_'.str_replace('_', '/', $class).'.php';
}

function __autoload($class) {
  @include_once(convert_class_to_filename($class));
}

//подключаемся к базе
include '../db_connect.php';
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//Спецификация файла *.f22
// 0-15 байт
// DB 07 08 00 02 00 10 00  0E 00 12 00 0C 00 5B 03
// Год (2) Месяц(1) 0(1) День недели(1) 0(1) Число(1) 0(1) Час GMT(1) 0(1) Мин(1) 0(1) Сек(1) 0(1) Микросек(2)
//                       В-00,Сб - 06
// 16-31 байт
// 80 03 00 00 00 00 00 00  00 00 00 00 00 00 00 00
// Размер файла с 32 байта (8) 0(8)
// 32-47
// BF 12 00 00 00 00 00 00  04 00 00 00 60 00 00 00
// Адрес чего-то в *.F00 с  № эл-та (4) Размер записи всех строк усилий в байтах, относящихся
// 32 байта (8)                          к данному эл-ту и сечению (4)
// 48-63
// 01 00 01 00 20 00 00 00  01 00 00 00        00      01     03          00
// Кол-во  №  Кол-во байт, Кол-во строк     № униф.  Вид   Кол-во       Наимен.
// сеч-й сеч-я описывающ.   формул у дан.    группы   (1)   усилий        усилий
// (2)    (2)  все формулы (4) эл-та и сеч.(4) (1)          в эл-те (1)  в стержнях(1)
//
// 1-N, 2-Mk, 4-M, 8-Q, 16-My, 32-Qz, 64-Mz, 128-Qy    есть - 1, нет - 0 формируется перемножением
//
// 64-79
// 00 08    00 00 00 00 00   01     00 00 00 00 00 00 00 00
// Наимен.      0(5)        Кол-во          0(6)
// усил.в                   сеч.в
// пласт                    эл-те
//  (2)                      (1)
//
//  1-Nx, 2-Ny, 4-Nz, 8-Txy, 16-Txz, 32- все вместе, 64-Mx, 128-My, 256-Mxy, 512-Qx, 1024-Qy    есть - 1, нет - 0 формируется перемножением
//
// Описание строк РСУ блоками по 32 байта. Общее кол-во байт по адресу 52-55
// 0-15
// 04 00 00 00   01 00   E8 03      01 00     01      00          00       00      00      00
// № эл-та (4)  № сеч. № крит.  № крит.-1  СТ   Сейсмика (1) Особая(1) Кран(1) Тип(1)   0(1)
//                (2)     (2)        (2)      (1) 0-нет,1-есть                   0-А,1-В
// 16-31
// 00 00 00 00 00 00 00 00  00 00 00 00 00 00 00 00
//           0(8)           Адрес в *.F22 с 32 байта (8)
//
//Усилия блоками по 8 байт * кол-во усилий в строке.
//Расчетные полные, расчетные длит, норм полные, норм длит. Потом следующая строка
//данного эл-та и сеч.
//
//Следущее сечение или элемент. Снова описание строк блоками по 32 байта.
//
//В конце файла 48 нулевых байтов




//функция декодирует 8 байтов в число
function bytes2double($number, $MNO, $STEP) {
//    echo 'Number has '.strlen($number).' bytes = '.$number.'<br/>';
    //переворачиваем строку
    $number = strrev($number);
    //создаем массив
    $mas = NULL;
    for ($i = 0; $i < 8; $i++)
        $mas[$i] = sprintf("%08b", ord($number[$i]));
//    var_dump($mas);
    //создаем 64 битную строку
    $s = implode('', $mas);
//    echo 'S = '.$s.'<br/>';
    //разделяем на биты знака, экспоненты и мантиссы
    $znak = substr($s, 0, 1);
//    echo 'ZNAK = '.$znak.'<br/>';
    $exp = substr($s, 1, 11);
//    echo 'EXP = '.bindec($exp).'<br/>';
    $mant = substr($s, 12, 52);
//    echo 'MANT = '.bindec($mant).'<br/>';
    //находим множители
    $a = pow(-1, bindec($znak));
//    echo 'A = '.$a.'<br/>';
    $b = pow(2, bindec($exp) - $STEP - 1023);
//    echo 'B = '.$b.'<br/>';
    $c = 1 + bindec($mant) / pow(2, 52);
//    echo 'C = '.$c.'<br/>';
    //считаем результат
//    echo 'RESULT = '.($a*$b*$c*$MNO).'<br/>';
    return $a * $b * $c * $MNO;
}

//послед-ть байт в целое число без знака, старшие байты в конце
function bytes2un_int ($bytes) {
    $uns_int64 = 0;
    for ($i=0;$i<=strlen($bytes)-1;$i++)
        $uns_int64 += ord($bytes[$i])*pow(256,$i);
    return $uns_int64;
}

if (isset($_FILES['F21_file']['name']) && $_FILES['F21_file']['name']!='' && 
    isset($_FILES['F22_file']['name']) && $_FILES['F22_file']['name']!='') {

        //очищаем базу
        mysql_query("TRUNCATE TABLE ".RSU);
        //читаем файл с коэффициентами к загружениям *.F22
        $fk = fopen($_FILES['F22_file']['tmp_name'], "rb");
    //    $fk = fopen("1.F22", "rb");
        //читаем первые 32 байтa с датой b размером файла
        fread($fk, 32);

        //читаем файл RSU
        $fd = fopen($_FILES['F21_file']['tmp_name'], "rb");
    //    $fd = fopen("1.F21", "rb");
        //читаем первые 16 байт с датой
        fread($fd, 16);
        //читаем 8 байт с размером файла преобразуем
        // в unsigned integer 64
        $F21_bytes_count = bytes2un_int(fread($fd, 8));
        echo 'FILE *.F21 has $F21_bytes_count bytes.<br/>';
        //читаем 8 пустых байтов
        fread($fd, 8);

        //Читаем пока не останется < 48 байтов - min размер описания
        while ( (32+$F21_bytes_count-ftell($fd) > 48) ) {

            ##############################
            //НАЧАЛО ПОВТОРЯЮЩЕЙСЯ ЧАСТИ//
            ##############################

            //адрес в файле *.F00 от 32 байта
            $F00_address = bytes2un_int(fread($fd, 8));
    //        echo "F00_adress - $F00_address.<br/>";
            //читаем 4 байта с номером эл-та
            fread($fd, 4);
            //читаем 4 байта - размер записи всех строк усилий в байтах
            $forces_bytes_count = bytes2un_int(fread($fd, 4));
    //        echo "Forces_Bytes_Count - $forces_bytes_count.<br/>";
            //читаем 2 байта - кол-во сечений в эл-те
            $section_count = bytes2un_int(fread($fd, 2));
    //        echo "Section_Count - $section_count.<br/>";
            //читаем 2 байта - номер сечения
            $section_number = bytes2un_int(fread($fd, 2));
    //        echo "Section_Number - $section_number.<br/>";
            //читаем 4 байта - кол-во байт, описывающий параметры всех строк с усилиями
            $params_bytes_count = bytes2un_int(fread($fd, 4));
    //        echo "Params_Bytes_Count - $params_bytes_count.<br/>";
             //читаем 4 байта - кол-во строк с усилиями у данного эл-та и сеч-ния
            $params_string_count = bytes2un_int(fread($fd, 4));
    //        echo "Params_String_Count - $params_string_count.<br/>";
             //читаем 1 байт - номер унифицированной группы
            $UNG = bytes2un_int(fread($fd, 1));
    //        echo "UNG - $UNG.<br/>";
            //читаем 1 байт - Вид
            $view = bytes2un_int(fread($fd, 1));
    //        echo "view - $view.<br/>";
            //читаем 1 байт - Кол-во усилий в эл-те
            $forces_count = bytes2un_int(fread($fd, 1));
    //        echo "Forces_Count - $forces_count.<br/>";

            //читаем 1 байт - Наименование усилий в стержнях
            $forces_names_bar = bytes2un_int(fread($fd, 1));
            //преобразуем описание имен усилий в бинарный
            $forces_names_bar = sprintf('%08b',$forces_names_bar);
    //        echo "Forces_Names_Bar - $forces_names_bar.<br/>";

            //читаем 2 байта - Наименование усилий в пластинах
            $forces_names_plate = bytes2un_int(fread($fd, 2));
            //преобразуем описание имен усилий в бинарный 16 байт, например 00000 11100 0 11111
            $forces_names_plate = sprintf('%016b',$forces_names_plate);
            //отрезаем лишние 5 байт, например 11100 0 11111
            $forces_names_plate = substr($forces_names_plate,5);
            //убираем средний байт, 1110011111
            $forces_names_plate = substr($forces_names_plate,0,5).substr($forces_names_plate,6);
    //        echo "Forces_Names_Plate - $forces_names_plate.<br/>";

            //читаем 14 байт - пустые - кол-во сеч-ний - пустые
            fread($fd, 14);
            //читаем описание параметров строк
            $params = fread($fd, $params_bytes_count);
            //читаем усилия
            $forces = fread($fd, $forces_bytes_count);

            ###################################################
            //читаем строки с параметрами блоками по 32 байта//
            ###################################################
            for ($i=0;$i<$params_string_count;$i++) {
                //создаем объект класса RSU
                $rsu = new RSU();
                //разбираем строку с параметрами
                //номер элемента
                $rsu->element = bytes2un_int(substr($params,0,4));
    //            echo "RSU->Element - $rsu->element.<br/>";
                //сечение
                $rsu->section = bytes2un_int(substr($params,4,2));
    //            echo "RSU->Section - $rsu->section.<br/>";
                //номер критерия 2 байта, номер критерия - 1 - 2 байта
                $rsu->criterion_number = bytes2un_int(substr($params,6,2));
    //            echo "RSU->Criterion_Number - $rsu->criterion_number.<br/>";
                //СТ
                $rsu->CT = bytes2un_int(substr($params,10,1));
    //            echo "RSU->CT - $rsu->CT.<br/>";

                //сейсмика
                if (bytes2un_int(substr($params,11,1)) > 0) $rsu->seismic = 1;
                else $rsu->seismic = 0;
    //            echo "RSU->Seismic - $rsu->seismic.<br/>";

                //особая
                if (bytes2un_int(substr($params,12,1)) > 0) $rsu->special = 1;
                else $rsu->special = 0;
    //            echo "RSU->Special - $rsu->special.<br/>";

                //крановая
                if (bytes2un_int(substr($params,13,1)) > 0) $rsu->impact = 1;
                else $rsu->impact = 0;
    //            echo "RSU->Impact - $rsu->impact.<br/>";

                //ТИП
                if (bytes2un_int(substr($params,14,1)) > 0) $rsu->type = 'B';
                else $rsu->type = 'A';
    //            echo "RSU->TYPE - $rsu->type.<br/>";

                //адрес в файле *.F22 от 32 байта
                $F22_address = bytes2un_int(substr($params,24,8));
    //            echo "F22_address - $F22_address.<br/>";
                //удаляем описание параметров данной строки - 32 байта
                $params = substr($params, 32);


                //вычисляем коэф. длит. части усилий
                //множитель 1.67013251783894
                //сдвиг экспоненты 14
                $rsu->long_part = bytes2double(substr($forces,$forces_count*8,8), 1, 0)/bytes2double(substr($forces,0,8), 1, 0);
    //            echo "RSU->Long_Part = $rsu->long_part<br/>";
                //вычисляем гамма f
                $rsu->factor_f = bytes2double(substr($forces,0,8), 1, 0)/bytes2double(substr($forces,$forces_count*8*2,8), 1, 0);
    //            echo "RSU->Factor_F = $rsu->factor_f<br/>";
                //считываем первые усилия - расчетные полные
        //        for ($k=0;$k<8;$k++)
        //            echo dechex(ord($forces[$k])).' ';
        //        echo '<br/>';
                //если это стержень
                if ($forces_names_bar > 0) {
                    for ($k=7;$k>=0;$k--)
                        if ($forces_names_bar[$k] == 1) {
                            $rsu->forces_bar_values[] =  bytes2double(substr($forces, 0, 8), 1, 0);
                            //обрезаем forces на 8 байтов
                            $forces = substr($forces,8);
                        }
                        else $rsu->forces_bar_values[] =  NULL;
                    //удаляем данную строку усилий, причем полные расчетные усилия уже удалены
                    $forces = substr($forces,8*$forces_count*3);

    //                echo "RSU->Forces_Bar_Names".  print_r($rsu->forces_bar_names)."<br/>";
    //                echo "RSU->Forces_Bar_Values".  print_r($rsu->forces_bar_values)."<br/>";
                }
                //если это пластина
                else {
                    for ($k=9;$k>=0;$k--)
                        if ($forces_names_plate[$k] == 1) {
                            $rsu->forces_plate_values[] =  bytes2double(substr($forces, 0, 8), 1, 0);
                            //обрезаем forces на 8 байтов
                            $forces = substr($forces,8);
                        }
                        else $rsu->forces_plate_values[] =  NULL;
                    //удаляем данную строку усилий, причем полные расчетные усилия уже удалены
                    $forces = substr($forces,8*$forces_count*3);

    //                echo "RSU->Forces_Plate_Names".  print_r($rsu->forces_plate_names)."<br/>";
    //                echo "RSU->Forces_Plate_Values".  print_r($rsu->forces_plate_values)."<br/>";
                }
            $rsu->UNG = $UNG; $rsu->view = $view;

            ###### F22 #######
            //читаем 4 байта - количество загружений в данной формуле
            $L_count = bytes2un_int(fread($fk, 4));
            //8 байт - значение критерия
            $rsu->criterion_value = sprintf("%01.2f",bytes2double(fread($fk, 8), 1, 0));
    //        echo "RSU->criterion_value = ". $rsu->criterion_value ."<br/>";
            //для каждого загружения в формуле читаем номер и значение множителя
            //записываем в массив
            $L_name_mas = array();
            for ($k=1;$k<=$L_count;$k++)
                $L_name_mas[] = bytes2un_int(fread($fk, 4));
            $L_koef_mas = array();
            for ($k=1;$k<=$L_count;$k++)
                $L_koef_mas[] = bytes2double(fread($fk, 8), 1, 0);

    //        echo "L_name_mas = ";print_r($L_name_mas);echo '<br/>';
    //        echo "L_koef_mas = ";print_r($L_koef_mas);echo '<br/>';

            for ($k=0;$k<$L_count;$k++) {
                if ($L_koef_mas[$k] >= 0 && $k>0) $rsu->formula .= '+';
                if (abs($L_koef_mas[$k]-1) > 0.001) $rsu->formula .= $L_koef_mas[$k].'*';
                $rsu->formula .= 'L'.$L_name_mas[$k];
            }
            //заменяем в L1-1*L2 (-1*) на (-)
            $rsu->formula = str_replace('-1*', '-', $rsu->formula);
    //        echo "RSU->Formula = ". $rsu->formula ."<br/>";
            ##################


            //преобразуем в РСУ величины, близкие
            //к нулю в 0
            foreach ($rsu->forces_bar_values as $key=>$value) {
               if (abs($value) < 0.001 && $value != NULL) $rsu->forces_bar_values[$key] = 0;
            }
    //        echo "RSU->Forces_Bar_Names".  print_r($rsu->forces_bar_names)."<br/>";
    //        echo "RSU->Forces_Bar_Values".  print_r($rsu->forces_bar_values)."<br/>";

              $uin = uniqid();
              $sql = "INSERT IGNORE INTO ".RSU." SET
                      uin = '$uin',
                      element = '$rsu->element',
                      UNG = '$rsu->UNG',
                      section = '$rsu->section',
                      CT = '$rsu->CT',
                      criterion_number = '$rsu->criterion_number',
                      criterion_value = '$rsu->criterion_value',
                      formula = '$rsu->formula',
                      view = '$rsu->view',
                      type = '$rsu->type',
                      seismic = '$rsu->seismic',
                      special = '$rsu->special',
                      impact = '$rsu->impact'";
              if (count($rsu->forces_bar_values)>0)
                   for ($k=0;$k<count($rsu->forces_bar_names);$k++)
                        if ($rsu->forces_bar_values[$k] !== NULL)
                            $sql .= ", ".$rsu->forces_bar_names[$k]." = '".
                                sprintf("%01.2f",$rsu->forces_bar_values[$k])."'";
              if (count($rsu->forces_plate_values)>0)
                   for ($k=0;$k<count($rsu->forces_plate_names);$k++)
                        if ($rsu->forces_plate_values[$k] !== NULL)
                            $sql .= ", ".$rsu->forces_plate_names[$k]." = '".
                                sprintf("%01.2f",$rsu->forces_plate_values[$k])."'";
    //          echo $sql.'<br/>';
              mysql_query($sql);


            }
        }

        fclose($fd);
        fclose($fk);

    //закрываем соединение с базой
    mysql_close($dbh);

    echo 'RSU were imported to MySQL.';
}
//если имя файла пустое
else echo "Не могу прочитать файлы *.F21,*.F22 !";
?>