<?php
class TNode {

    //разбирает документ с узлами полученный из
    // SCAD в файле TXT без повторителей
    function get_from_scad_txt ($s) {
        //разделяем документ на строки и удаляем последнюю
        $mas = explode('/',$s);
        array_splice($mas,-1,1);
        //перебор строк
        foreach ($mas as $value) {
            $node = new Node();
            $node->get($value);
            //запись в базу данных
            mysql_query("INSERT INTO ".nodes." SET
                      removed = '$node->removed',
                      x = '$node->x',
                      y = '$node->y',
                      z = '$node->z'"
            );
        }
    }


    //создает документ с узлами полученный для
    // SCAD в файле TXT без повторителей
    // (НОМЕР/ *RETURN* )
    function set_to_scad_txt () {
        $s='';
        $sql = "SELECT * FROM ".nodes." ORDER BY id";
        mysql_query($sql);
        switch (mysql_errno()) {
         case 1146: echo "<b>Table ".nodes." doesn't exist. Please create DB.</b><br>";break;
         default:
            if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0)
               while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                   $s .= sprintf("%01.6f", $row['x']).' '.
                         sprintf("%01.6f", $row['y']).' '.
                         sprintf("%01.6f", $row['z'])."/\r\n";
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
            $node = new Node();

            $value = trim($value);
            if ($value != '') {
                //замена двойных пробелов
                $value = preg_replace("|\s{2,}|", ' ', $value);
                $value = explode(' ', $value);
                $node->id = $value[0];
                $node->removed = FALSE;
                $node->x = $value[1]/1000;
                $node->y = $value[2]/1000;
                $node->z = $value[3]/1000;

                //запись в базу данных
                mysql_query("INSERT INTO ".nodes." SET
                          id = '$node->id',
                          removed = '$node->removed',
                          x = '$node->x',
                          y = '$node->y',
                          z = '$node->z'"
                );
            }
        }
    }
}
?>