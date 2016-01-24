<?php
namespace php\classes;

class TDoc {
    public $number; // номер документа
    public $byte_offset; // offset
    public $byte_count; // количество байт
    public $body; // тело документа в бинарном виде
    // тело файла, находящегося между документами,
    // если такое существует, в бинарном виде
    public $appendix; 
    
    public function __construct($number, $offset, $count) {
        $this->number = $number;
        $this->byte_offset = $offset;
        $this->byte_count = $count;
    }
}

