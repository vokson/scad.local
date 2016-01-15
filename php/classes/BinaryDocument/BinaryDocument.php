<?php

namespace php\classes\BinaryDocument;

class BinaryDocument {
    private $number; 
    private $address;
    private $bytesCount;
    private $body;
    private $appendix; 
    
    public function __construct($number, $address, $bytesCount) {
        $this->number = $number;
        $this->address = $address;
        $this->bytesCount = $bytesCount;
    }
}

