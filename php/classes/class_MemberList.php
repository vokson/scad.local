<?php
class MemberList {

    public $list = array(); //список элементов
    
    /**
    // Получает список элементов вида '129 r 138 3 140 142 155-157'
    // Выдает массив с номерами элементов
     *
     * @param String $s
     * @return Array
     * @assert ('1') ==  array (1)
     * @assert ('129 r 133 2') ==  array (129,131,133)
     * @assert ('2 501-503 505') ==  array (2,501,502,503,505)
     * @assert (' 129 r 138 3 140   142 155-157 ') ==  array (129,132,135,138,140,142,155,156,157)
     */
    function get_member_list ($s) {
        $list = NULL;
        //удаляем пробелы из начала и конца
        $s = trim($s);
        //удаляем переносы строк
        $s = preg_replace("|\r+\n+|", ' ', $s);
        //удаляем парные пробелы между числами
        $s = preg_replace('|\s+|', ' ', $s);

//        echo $s.'<br/>';
        $s = explode(' ', $s);
        $r = FALSE;
        for ($i=0;$i<count($s);$i++)
            if (strpos($s[$i],'-') === FALSE)
               if ($s[$i] != 'r') $list[] = $s[$i];
               else {
                  $r = $s[$i+2];
                  $beg = $s[$i-1]; $end = $s[$i+1];
                  for ($k=$beg+$r;$k<=$end;$k=$k+$r) $list[] = $k;
                  $i += 2;
               }
            //Если 155-157
            else {
                list($beg,$end) = explode('-',$s[$i]);
                for ($k=$beg;$k<=$end;$k++) $list[] = $k;
            }

        //сортируем массив по возрастанию
        sort($list,SORT_NUMERIC);
        $this->list = (array)$list;
        return $this->list;
    }

    /**
    // Получает массив с номерами элементов
    // Выдает список элементов вида '129 r 138 3 140 142 155-157'
     *
     * @param Array $list
     * @return String
     * @assert (array(1)) ==  '1'
     * @assert (array(1,2,3)) ==  '1-3'
     * @assert (array(1,3,5,7,10)) ==  '1 r 7 2 10'
     * @assert (array(1,2,3,5,7,9,10,11,12,13)) ==  '1-3 5 r 9 2 10-13'
     * @assert (array(1,2,3,6,8,10,13,14,15,16)) ==  '1-3 6 r 10 2 13-16'
     * @assert (array(1,65,72)) ==  '1 65 72'
     */
    function set_member_list ($list = NULL) {
        if ($list === NULL) $list = $this->list;
        //Если в массиве только 1 элемент
        if (count($list) == 1) return $list[0];

        //сортируем массив по возрастанию
        sort($list,SORT_NUMERIC);
        // Создаем массив приращений между числами
        for ($i=1;$i<count($list);$i++)
            $inc[$i] = $list[$i]-$list[$i-1];
        $inc[0] = -1;
    //    print_r($inc);

        //Создаем строку
        for ($i=0;$i<count($list);$i++) {
            //если текущее != последующему
            if ($inc[$i] != $inc[$i+1])
                //если текущее = предыдущему
                if ($inc[$i] == $inc[$i-1]) {
                    if ($inc[$i] != 1) {$s .= ' r '.$list[$i].' '.$inc[$i].' '.$list[$i+1]; $i++;}
                    else {$s .= '-'.$list[$i].' '.$list[$i+1]; $i++;}
                //если текущее != предыдущему
                } else {
                    //если текущее не = предыдущему и не = последующему
                    $s .= ' '.$list[$i];
                }
        }

        return (string)trim($s);
    }
}
?>