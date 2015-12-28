<?php
class TMember {

    //разбирает документ с узлами полученный из
    // SCAD в файле TXT без повторителей
    function get_from_scad_txt ($s) {
       
        //разделяем документ на строки и удаляем последнюю
        $mas = explode('/',$s);
        array_splice($mas,-1,1);
        //перебор строк
        
        foreach ($mas as $value) {
            $member = new Member();
            $member->get($value);
            //запись в базу данных
            $sql = 'INSERT INTO '.members.'
                    (
                        removed,
                        type,
                        section';
            for ($i=1;$i<=count($member->list);$i++)
                $sql .= ',N'.$i;
            $sql .= ") VALUES (
                        $member->removed,
                        $member->type,
                        $member->section,
                      ".implode(',',$member->list)."
                    )";
            mysql_query($sql);
        }
    }


    //создает документ с узлами полученный для
    // SCAD в файле TXT без повторителей
    // (НОМЕР/ *RETURN* )
    function set_to_scad_txt () {
        $s='';
        $sql = "SELECT * FROM ".members." ORDER BY id";
        mysql_query($sql);
        switch (mysql_errno()) {
         case 1146: echo "<b>Table ".members." doesn't exist. Please create DB.</b><br>";break;
         default:
            if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0)
               while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                   $s .= implode(' ',array($row['type'],$row['section']));
                   //пишем номера узлов, какие есть
                   for ($i=1;$i<=8;$i++)
                       if ($row['N'.$i] != NULL) $s .= ' '.$row['N'.$i];
                   $s .= "/\r\n";
               }
        }
         return $s;
    }

    //разбирает документ с узлами полученный из
    // STAAD файла STD
    function get_from_staad_std ($s) {
        //разделяем документ на строки
        $mas = explode("\r\n",$s);

        //перебор строк
        foreach ($mas as $value) {
            $member = new Member();

            $value = trim($value);
            if ($value != '') {
                //замена двойных пробелов
                $value = preg_replace("|\s{2,}|", ' ', $value);
                $value = explode(' ', $value);
                $member->id = $value[0];
                $member->removed = 0;
                $member->type = 5;
                $member->section = 0;
                $member->list[0] = $value[1];
                $member->list[1] = $value[2];
                //запись в базу данных
                $sql = "INSERT INTO ".members."
                    (
                        id,
                        removed,
                        type,
                        section,
                        N1,
                        N2
                    )
                    VALUES
                    (
                        $member->id,
                        $member->removed,
                        $member->type,
                        $member->section,
                      ".$member->list[0].",
                      ".$member->list[1]."
                    )";
                mysql_query($sql);
            }
        }
        //присваиваем узлам новые номера
        mysql_query('UPDATE '.nodes.' SET id =(SELECT @a:= @a + 1 from (SELECT @a:= 0) S) ORDER BY id');
        //присваиваем элементам новые номера
        mysql_query('UPDATE '.members.' SET id =(SELECT @a:= @a + 1 from (SELECT @a:= 0) S) ORDER BY id');
    }
}
?>