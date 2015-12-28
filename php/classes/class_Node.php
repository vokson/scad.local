<?php
class Node {

    public $id;
    public $x; //координата Х
    public $y; //координата Y
    public $z; //координата Z
    public $removed; //удален ли узел

    public function __construct($id=NULL, $removed = 0, $x=0, $y = 0, $z = 0) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->id = $id;
        $this->removed = $removed;
    }

    /** Создает узел из строки описания
     *
     * @param String $s
     * @return Array
     * @assert (' 56.33') ==  array (56.33,0,0)
     * @assert ('73.44 0.3 ') ==  array (73.44,0.3,0)
     * @assert ('23.44 54.2 3') ==  array (23.44,54.2,3)
     * @assert ('37.5 0 0.3') ==  array (37.5,0,0.3)
     */

    function get ($s) {
        $s = trim($s);
        $s = explode(' ', $s);
        $this->x = $s[0];
        if (isset($s[1])) $this->y = $s[1];
        if (isset($s[2])) $this->z = $s[2];

        return array($this->x,  $this->y, $this->z);
    }

    function set () {
        return implode(' ',array($this->x,  $this->y, $this->z));
    }
   
}
?>