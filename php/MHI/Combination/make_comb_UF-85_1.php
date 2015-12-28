<?php

//пишет комбинацию в базу данных
function write_comb($num, $mas) {
    for ($k=0;$k<count($mas);$k++)
        mysql_query("INSERT INTO ".combinations." SET comb = $num,
                koef = ".($k+1).", value = ".$mas[$k]); 
    $num++;
}

//создает комбинации
function make_comb($num, $mas) {
   
    
    //Erection Stage
    for ($i=0;$i<4;$i++) {
        //обнуляем комбинацию
        $comb = array_fill(0,23,0);
        //пишем нужные коэффициенты
        $comb[0] = $mas[0];
        $comb[2] = $mas[2];
        $comb[5] = $mas[5];
        $comb[19+$i] = $mas[19+$i];
        //пишем комбинацию в базу
        write_comb(&$num, $comb);
    }

    //Operation Stage
    for ($i=0;$i<2;$i++) {
        for ($k=0;$k<4;$k++) {
            //обнуляем комбинацию
            $comb = array_fill(0,44,0);
            //пишем нужные коэффициенты
            $comb[0] = $mas[0];
            $comb[1] = $mas[1];
            $comb[2] = $mas[2];
            $comb[3] = $mas[3];
            $comb[4] = $mas[4];
            $comb[6] = $mas[6]; //TEMP
            if (fmod($k,2) != 0) $comb[6] *= -1; //TEMP
            $comb[17+$i] = $mas[17+$i];
            $comb[19+$k] = $mas[19+$k];
            //пишем комбинацию в базу
            write_comb(&$num, $comb);
        }
    }

    //Maintanance Stage 
    for ($i=0;$i<2;$i++) {
        for ($j=0;$j<4;$j++) {
            for ($k=0;$k<4;$k++) {
                //обнуляем комбинацию
                $comb = array_fill(0,44,0);
                //пишем нужные коэффициенты
                $comb[0] = $mas[0];
                $comb[1] = $mas[1];
                $comb[2] = $mas[2];
                $comb[5] = $mas[5];

                $comb[7+$j] = $mas[7+$j]; //HOIST
                $comb[11+fmod($j,2)] = $mas[11+fmod($j,2)]; //BUNDLE
                $comb[17+$i] = $mas[17+$i]; //SNOW
                $comb[19+$k] = $mas[19+$k]; //WIND
//                if ( % 2 != 0) $comb[25+$k] *= -1;
//
//                $comb[39+$i] = $mas[39+$i];
//
//                $comb[43] = $mas[43];
                //пишем комбинацию в базу
                write_comb(&$num, $comb);
            }
        }
    }
    
   
}

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

include_once '../db_connect.php';
//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
mysql_query("SET NAMES utf8");

mysql_query("TRUNCATE TABLE ".combinations);

  $fac_factors = array(1.00, 0.90, 0.95, 0.95, 0.95, 0.95, 0.95, 0.90, 0.90, 0.90, 0.90, 0.90, 0.90, 0.00, 0.00, 0.00, 0.00, 0.90, 0.90, 0.90, 0.90, 0.90, 0.90);
$unfac_factors = array(0.91, 0.75, 0.86, 0.86, 0.86, 0.86, 0.86, 0.82, 0.82, 0.82, 0.82, 0.56, 0.56, 0.00, 0.00, 0.00, 0.00, 0.45, 0.45, 0.65, 0.65, 0.65, 0.65);

$comb_number = 1;


make_comb(&$comb_number, $fac_factors);    //FACTORED
make_comb(&$comb_number, $unfac_factors);  //UNFACTORED

echo ($comb_number-1).' combinations were created.<br/>';

//закрываем соединение с базой
mysql_close($dbh);
?>