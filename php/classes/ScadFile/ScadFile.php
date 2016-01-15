<?php

namespace php\classes\ScadFile;

class ScadFile {

    private $binaryFileContent;
    private $cursor;
    private $docs = array();

    private $bytesCountByType = array('S' =>  2, 'L' => 4);
    
    const CORRECT_HEADER_WORD = '*Schema*';
    const FOOTER_VARIABLE_ADDRESS = 8;

    public function __construct($content) {

        $this->binaryFileContent = $content;

        $this->checkHeaderWordCorrect();
        $this->uploadDocuments();
    }

    private function checkHeaderWordCorrect() {
        $headerWord = substr($this->binaryFileContent, 0, strlen(self::CORRECT_HEADER_WORD));
        if (strcmp($headerWord, self::CORRECT_HEADER_WORD) != 0) {
            throw new WrongFileFormatException;
        }
    }

    private function uploadDocuments() {
        
        $this->cursor = $this->getFileFooterAddress();
        while ($this->addNewDocumentUsingDescriptionInCursorPosition()) {}
        
        $this->isCursorInTheEndPosition();
    }

   private function getFileFooterAddress() {
        $this->cursor = self::FOOTER_VARIABLE_ADDRESS;
        return $this->unpackIntValue();
    }

    private function addNewDocumentUsingDescriptionInCursorPosition() {
       
        $number = $this->unpackShortValue();
        
        if ($this->isNumberOfDocumentFinal($number)) {
            return FALSE;
        }

        $address = $this->unpackLongValue();
        $bytesCount = $this->unpackLongValue();
        
        $this->docs[] = new \php\classes\BinaryDocument\BinaryDocument($number, $address, $bytesCount);
        return TRUE;
    }
    
    private function isCursorInTheEndPosition() {
        if ($this->cursor != strlen($this->binaryFileContent)) {
            throw new WrongFileFormatException;
        }
    }

    private function isNumberOfDocumentFinal($number) {
        return ($number == 0);
    }
    
    /*
     * Since unpack('Q') is not work on PHP 32-bit,
     * read 32-bit integer and 32-bit zeros
     */
    private function unpackLongValue() {
        $intValue = $this->unpackIntValue();
        $zeroValue = $this->unpackIntValue();
        
        return $intValue;
    }
    
    private function unpackIntValue() {
        return $this->unpackValue('L');
    }

    private function unpackShortValue() {
        return $this->unpackValue('S');
    }

    private function unpackValue($type) {
        $bytesCount = $this->bytesCountByType[$type];
        
        if (strlen($this->binaryFileContent) < ($this->cursor + $bytesCount)) {
            throw new WrongFileFormatException;
        }

        $variableBinaryData = substr($this->binaryFileContent, $this->cursor, $bytesCount);
        $unpackedArray = unpack($type, $variableBinaryData);
        
        $this->cursor += $bytesCount;

        return $unpackedArray[1];
    }

}
