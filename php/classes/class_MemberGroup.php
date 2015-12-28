<?php
require_once dirname(__FILE__) .DIRECTORY_SEPARATOR. "class_MemberList.php";

class MemberGroup extends MemberList {
    public $appointment; //1 - группа для побора арматуры, 2 - общего назначения
    public $type; //1 - стержни, 2 - пластины (используется только для подбора арматуры)
    public $name; //Имя группы

    /**
    * разбираем группу элементов и группы для подбора арматуры
    * Документ 47
    * Пример
    * 2 1 Name="+4.000 - B1"  : 29 30
    *  1 - группа для подбора арматуры, 2 - общего назначения
    *  1 - стержни, 2 - пластины (используется только для подбора арматуры)
    *  если общего назначения - тип=1
    *   Name="Имя" : Список элементов
    *
    * @param String $s
    * @return Array
    */
    function get ($s) {
        $s = trim($s);
        //удаляем переносы строк
        $s = preg_replace("|\r+\n+|", ' ', $s);
        
//        echo "GET(s) = ".$s.'</br>';
        preg_match('|(\d{1})\s+(\d{1})\s+Name="(.*)"\s+:(.*)|', $s, $matches);
        $this->appointment = (int)$matches[1];
//        echo 'Appointment = '.$this->appointment.'</br>';
        $this->type = (int)$matches[2];
//        echo 'Type = '.$this->type.'</br>';
        $this->name = (string)$matches[3];
//        echo 'Name = '.$this->name.'</br>';
        $this->list = (array)$this->get_member_list($matches[4]);
//        echo 'List = '.$matches[4].'</br>';
    }

    function set () {
        $s = $this->appointment.' '.$this->type.' Name="'.$this->name.'" : '.$this->set_member_list();
        return $s;
    }
}
?>