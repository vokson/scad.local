<?php

class TMemberGroupSteel11 {

    //разбирает документ с группами подбора полученный из
    // SCAD11 в файле SPR
    //Спецификация
    //Начало 28 документа
//    кол-во    Тип   Описание
//     байт 
//      80    string  C255, остальные нулевые
//      8     double  сопротивление стали
//      8     double  gamma_N
//      8     double  gamma_C
//      8     double  FC
//      1     -       нулевой байт
//      4     int     кол-во групп
//      Далее идет описание групп
    function get_from_scad_spr($s) {
        mysql_query("TRUNCATE TABLE " . member_group_for_steel);
        $group = new MemberGroupSteel11();

        //начинаем чтение с кол-ва групп
        $pos = 80 + 4 * 8 + 1;
        $group_count = unpackInt_4(substr($s, $pos, 4));
        $pos += 4;
        //перебор строк
        for ($i = 1; $i <= $group_count; $i++) {
            //кол-во байт в названии группы
            $group_name_byte_count = unpackInt_2(substr($s, $pos, 2));
            //кол-во элементов
            $member_count = unpackInt_4(substr($s, $pos + 2 + $group_name_byte_count + 80 + 4 * 8 + 1 + 2 + 2 * 8 + 64, 4));
            //получаем часть данных, описание текущей группы

            $gap = 2 + $group_name_byte_count + 80 + 4 * 8 + 1 + 2 + 2 * 8 + 64 + 4 + $member_count * 4;
            $group->get_from_spr(substr($s, $pos, $gap));
            $pos += $gap;

            //запись в базу данных
            mysql_query("INSERT IGNORE INTO " . member_group_for_steel . " SET
                      steel = '$group->steel',
                      Ry = '$group->Ry',
                      gamma_c = '$group->gamma_c',
                      FC = '$group->FC',
                      group_type = '$group->group_type',
                      mu_XZ = '$group->mu_XZ',
                      mu_XY = '$group->mu_XY',
                      name = '" . iconv('Windows-1251', 'UTF-8', $group->name)  . "',
                      list = '" . implode(' ', $group->list) . "'"
            );
        }
    }

    //разбирает документ с узлами полученный из
    // SCAD в файле TXT без повторителей
    function get_from_scad_txt($s) {
        mysql_query("TRUNCATE TABLE " . member_group_for_steel);
        $group = new MemberGroupSteel11();
        //разделяем документ на строки и удаляем последнюю
        $mas = explode('/', $s);
        array_splice($mas, -1, 1);
        //перебор строк
        foreach ($mas as $value) {
            $group->get_from_txt($value);
            //запись в базу данных
            mysql_query("INSERT IGNORE INTO " . member_group_for_steel . " SET
                      steel = '$group->steel',
                      Ry = '$group->Ry',
                      gamma_c = '$group->gamma_c',
                      FC = '$group->FC',
                      group_type = '$group->group_type',
                      mu_XZ = '$group->mu_XZ',
                      mu_XY = '$group->mu_XY',
                      name = '" . iconv('Windows-1251', 'UTF-8', $group->name) . "',
                      list = '" . implode(' ', $group->list) . "'"
            );
        }
    }

    //создает документ с группами для подбора стали
    // SCAD в файле TXT без повторителей
    // (НОМЕР/ *RETURN* )
    function set_to_scad_txt() {
        $s = '';
        $sql = "SELECT * FROM " . member_group_for_steel . " ORDER BY id";
        mysql_query($sql);
        switch (mysql_errno()) {
            case 1146: echo "<b>Table " . member_group_for_steel . " doesn't exist. Please create DB.</b><br>";
                break;
            default:
                if (mysql_errno() > 0) {
                    echo mysql_errno() . '  ' . mysql_error() . '<br>';
                }
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        //первая строка
                        $s .= '"' . $row['steel'] . '" ' . sprintf("%01.3f", $row['Ry']) .
                                ' 0 ' . sprintf("%01.2f", $row['gamma_c']) . ' ' . $row['FC'] . "\r\n";
                        //вторая строка
                        $s .= $row['group_type'] . ' ' . sprintf("%01.2f", $row['mu_XZ']) . ' ' . sprintf("%01.2f", $row['mu_XY']) . "\r\n";
                        //третья строка
                        $s .= ' 0     0.     0.     0.     0.     0.     0.     0.      0      0' . "\r\n";
                        //четвертая строка
                        $s .= 'Name="' . iconv('UTF-8', 'Windows-1251', $row['name']) . '" : ' . $row['list'] . "/\r\n";
                    }
                }
        }
        return $s;
    }

    // создает документ с группами для подбора стали
    // SCAD в файле SPR
    function set_to_scad_spr() {
        $s = '';
        //формируем начало 28 документа
        //класс стали - 80 байт
        $s .= pack('a80', 'C255');
        //сопротивление стали
        $s .= pack('d', 240.26292);
        //0.95 - неизвестно что
        $s .= pack('d', 0.95);
        //gamma_C
        $s .= pack('d', 0.95);
        //гибкость
        $s .= pack('d', 400);
        //нулевой байт
        $s .= pack('a1', '');

        $sql = "SELECT * FROM " . member_group_for_steel;
        mysql_query($sql);
        switch (mysql_errno()) {
            case 1146: echo "<b>Table " . member_group_for_steel . " doesn't exist. Please create DB.</b><br>";
                break;
            default:
                if (mysql_errno() > 0)
                    echo mysql_errno() . '  ' . mysql_error() . '<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    //количество групп
                    $s .= pack('V', mysql_num_rows($result));
                    while ($row = mysql_fetch_object($result, 'MemberGroupSteel11')) {
                        $row->list = explode(' ', $row->list);
                        $s .= $row->set_to_spr();
                    }
                }
        }
        return $s;
    }

}

?>