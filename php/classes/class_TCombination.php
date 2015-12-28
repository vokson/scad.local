<?php
class TCombination {
    //разбирает документ с комбинациями полученный из
    // SCAD в файле SPR
    //Спецификация
    //36 документ
//    кол-во    Тип   Описание
//     байт 
//      1     00      нулевые
//      4     int     кол-во комбинаций
//      2     00      нулевые
//      описание комбинации
//      2     00      нулевые
//      описание комбинации
//      ...

    
    function get_from_scad_spr ($s) {
        mysql_query("TRUNCATE TABLE ".combinations);
        $comb = new Combination();
        
        //начинаем чтение с кол-ва комбинаций
       $pos = 1;
       
       $comb_count = bytes2un_int(substr($s, $pos, 4)); $pos += 4;
        //перебор строк
       for ($i=1;$i<=$comb_count;$i++) {
           //2 нулевых байта перед комбинацией
           $pos += 2;
           //кол-во коэффициентов в комбинации
           $koef_count = bytes2un_int(substr($s, $pos, 4));
           //кол-во байтов в комбинации
           $gap = 4+$koef_count*8;
           $comb->get_from_spr(substr($s, $pos, $gap)); $pos += $gap;
           
           for ($k=1; $k<=count($comb->factors); $k++)
                //запись в базу данных
                mysql_query("INSERT IGNORE INTO ".combinations." SET 
                   comb = $i, koef = $k, value = ".$comb->factors[$k]);
       }
    }
    
    
    
    // создает документ с комбинациями
    // SCAD в файле SPR
    function set_to_scad_spr () {
        $s='';
        //формируем начало 36 документа
        //нулевой байт
        $s .= pack('a1','');
        
        $sql = "SELECT MAX(comb) FROM ".combinations;
        mysql_query($sql);
        switch (mysql_errno()) {
         case 1146: echo "<b>Table ".combinations." doesn't exist. Please create DB.</b><br>";break;
         default:
            if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $comb_count = mysql_result ($result, 0, 0);
                if ($comb_count != NULL) {
                    //пишем кол-во комбинаций
                    $s .= pack('V',(string)$comb_count);
                   
                    for ($i=1;$i<=$comb_count;$i++) {
                        //2 нулевых байта
                        $s .= pack('a2','');

                        $comb = new Combination();
                        $sql = "SELECT * FROM ".combinations." WHERE comb = $i";
//                        echo $sql.'<br/>';
                        $result = mysql_query($sql);
                        if (mysql_num_rows($result) > 0)
                            while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
                                $comb->factors[$row['koef']] = $row['value'];
                        //пишем комбинацию
                        $s .= $comb->set_to_spr();
                    }
                } else $s .= pack('a4',''); //кол-во комбинаций = 0 int 4 байта
            } 
        }
        return $s;
    }
  
}


?>