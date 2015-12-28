<?php
class TMemberGroup {
    //разбирает документ с узлами полученный из
    // SCAD в файле TXT без повторителей
    function get_from_scad_txt ($s) {
        mysql_query("TRUNCATE TABLE ".member_group);
        $group = new MemberGroup();
        //разделяем документ на строки и удаляем последнюю
        $mas = explode('/',$s);
        array_splice($mas,-1,1);
        //перебор строк
        foreach ($mas as $value) {
            $group->get($value);
            //запись в базу данных
            mysql_query("INSERT INTO ".member_group." SET
                      appointment = '$group->appointment',
                      type = '$group->type',
                      name = '$group->name',
                      list = '".implode(' ',$group->list)."'"
            );
        }
    }
  
}
?>