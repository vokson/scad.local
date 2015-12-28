<?php
require_once dirname(__FILE__) .DIRECTORY_SEPARATOR.  "class_MemberList.php";

class Combination {
    public $factors; //массив с коэффициентами
    
    
        /**
    * разбираем комбинацию
    * Документ 36
//      4     int     кол-во коэффициентов
//      8     double  коэффициенты
//      Сначала идут коэффициенты для загружений,
//      потом для комбинаций
    *
    * @param String $s
    * @return Array
    */
    function get_from_spr ($s) {
        $pos = 0;
        //кол-во коэффициентов в комбинации
        $koef_count = bytes2un_int(substr($s, $pos, 4)); $pos +=4;
        $this->factors = array();
        for ($i=1;$i<=$koef_count;$i++) {
            $this->factors[$i] = bytes2double(substr($s, $pos, 8),1,0);
            $pos += 8;
        }
    }

   //создаем комбинацию
    function set_to_spr () {
        //создаем строку в бинарном виде
        $s = '';
        //кол-во коэффициентов в комбинации
        $s .= pack('V',(string)count($this->factors));
        //пишем коэффициенты
        foreach ($this->factors as $value)
            $s .= pack('d',(double)$value);
        return $s;
    }
 
}
?>