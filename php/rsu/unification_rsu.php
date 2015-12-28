<?php
//Максимальное время работы скрипта 1 час
set_time_limit(3600);

function group_unification ($id, $list) {
    if (isset($list) && $list != '') {
        //заменяем в list пробелы на запятые
        $list = str_replace(' ', ',', $list);
        $sql = "SELECT * FROM ".RSU." WHERE element IN (".$list.")";
//        echo $sql.'<br/>';
        $mas_unif = array();
        mysql_query($sql);
        switch (mysql_errno()) {
         case 1146: echo "<b>Table ".RSU." doesn't exist. Please create DB.</b><br>";break;
         default:
            if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0)
               while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                    //Выбираем только с максимальным значением критерия
                    if ( !isset($mas_unif[$row['criterion_number']]) ||
                         ($mas_unif[$row['criterion_number']]['criterion_value'] < $row['criterion_value'])  ) {
                            //Записываем значения критерия и uin в массив
                            $mas_unif[$row['criterion_number']]['criterion_value'] = $row['criterion_value'];
                            $mas_unif[$row['criterion_number']]['uin'] = $row['uin'];
                    }
        }
        //запись в таблицу
        foreach ($mas_unif as $key => $value) {
//             echo '<br/>';
//            print_r($mas_unif[$i]);
//             echo '<br/>';
            $sql = 'INSERT INTO '.RSU_UNIF.' SELECT * FROM '.RSU." WHERE uin = '".$mas_unif[$key]['uin']."'";
            mysql_query($sql);
            $sql = 'UPDATE '.RSU_UNIF." SET UNG = $id WHERE uin = '".$mas_unif[$key]['uin']."'";
            mysql_query($sql);
        }

    }
}


//подключаемся к базе
include '../db_connect.php';
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//очищаем таблицу c унифицированными РСУ
mysql_query('TRUNCATE TABLE '.RSU_UNIF);
//унификация по группам подбора стали
$sql = "SELECT * FROM ".member_group_for_steel;
mysql_query($sql);
switch (mysql_errno()) {
 case 1146: echo "<b>Table ".member_group_for_steel." doesn't exist. Please create DB.</b><br>";break;
 default:
    if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0)
       while($row = mysql_fetch_array($result, MYSQL_ASSOC))
           group_unification ($row['id'], $row['list']);
}

//закрываем соединение с базой
mysql_close($dbh);

echo 'Унификация завершена.';
?>