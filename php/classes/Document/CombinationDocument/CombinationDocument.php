<?php

namespace php\classes\Document\CombinationDocument;

use php\classes\BinaryDataContainer\BinaryDataContainer;
use php\classes\BinaryDataContainer\WrongDataFormatException;

class CombinationDocument {

    const COMBINATION_COUNT_ADDRESS = 1;
    const COMBINATION_BLOCK_START_ADDRESS = 9;
    const DOUBLE_TYPE_SIZE = 8;
    
    private $binaryDataContainer;

    public function binaryDataToArray($data) {

        $this->binaryDataContainer = new BinaryDataContainer($data);
        $combinationCount = $this->getCombinationCount();
        
        $this->binaryDataContainer->setCursor(self::COMBINATION_BLOCK_START_ADDRESS);

        $combinations = array();
        for ($i = 0; $i < $combinationCount; $i++) {
            $combinations[] = $this->readSingleCombination();
        }

        if (!$this->binaryDataContainer->isCursorInTheEndPosition()) {
            throw new WrongDataFormatException;
        }
        
        return $combinations;
    }

    private function getCombinationCount() {
        $this->binaryDataContainer->setCursor(self::COMBINATION_COUNT_ADDRESS);
        return $this->binaryDataContainer->unpackIntValue();
    }
    
    private function readSingleCombination() {
        
        // Pass NULL byte
        $this->binaryDataContainer->shiftCursor(1);
        
        
        $factorCount = $this->binaryDataContainer->unpackIntValue();
        
        $combinationFactors = [];
        for ($i=0; $i<$factorCount; $i++) {
            $combinationFactors[] = $this->binaryDataContainer->unpackDoubleValue();
        }

        return $combinationFactors;
    }

    public function arrayToBinaryData($array) {
        
        $this->binaryDataContainer = new BinaryDataContainer('');
        
        $binaryData = '';
        $binaryData .= "\x00";
        $binaryData .= $this->binaryDataContainer->packIntValue(count($array));
        
        $totalFactorCount = array_sum(array_map("count", $array));
        $binaryData .= $this->binaryDataContainer->packIntValue($totalFactorCount * self::DOUBLE_TYPE_SIZE);
        
        foreach ($array as $row) {
            $binaryData .= $this->writeSingleCombination($row);
        }
        
        return $binaryData;
    }
    
    private function writeSingleCombination($factorArray) {

        $binaryData = '';
        
        $binaryData .= "\x00";
        $binaryData .= $this->binaryDataContainer->packIntValue(count($factorArray));
        
        foreach ($factorArray as $factor) {
            $binaryData .= $this->binaryDataContainer->packDoubleValue($factor);
        }
        
        return $binaryData;
    }

}
