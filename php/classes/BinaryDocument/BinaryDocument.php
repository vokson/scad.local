<?php

namespace php\classes\BinaryDocument;

use php\classes\PropertyContainer\PropertyContainer;

class BinaryDocument extends PropertyContainer{

    public function __construct($number, $address, $bytesCount) {
        $this->number = $number;
        $this->address = $address;
        $this->bytesCount = $bytesCount;
    }
}
