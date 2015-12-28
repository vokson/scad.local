<?php
class Member {

    public $id; //номер элемента
    public $removed; //удален ли узел
    public $section; //тип жесткости
    public $type; //тип жесткости
    public $list = array(); //список элементов

    public function __construct($id=NULL, $removed = 0,
            $type = 0, $section = 0, $list = array()) {
        $this->id = $id;
        $this->removed = $removed;
        $this->type = $type;
        $this->section = $section;
        $this->list = $list;
    }

    /** Создает элемент из строки описания
     *
     * @param String $s
     * @return Array
     * @assert ('36 0 5 6 7 8 9 10 11 12') ==  array (36,0,array(5, 6, 7, 8, 9, 10, 11, 12))
     * @assert ('44 0 1 2 3 4') ==  array (44,0,array(1, 2, 3, 4))
     * @assert ('55 10 5 6') ==  array (55,10,array(5, 6))
     */

    function get ($s) {
        $s = explode(' ', trim($s));
        $this->type = $s[0];
        $this->section = $s[1];
        $this->list = array();
        for ($i=2;$i<count($s);$i++)
            $this->list[] = $s[$i];

        return array($this->type,  $this->section, $this->list);
    }

    function set () {
        return implode(' ',array($this->type,  $this->section, implode(' ',$this->list)));
    }
   
}
?>