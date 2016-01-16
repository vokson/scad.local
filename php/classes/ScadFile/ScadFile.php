<?php

namespace php\classes\ScadFile;

use php\classes\BinaryDocument\BinaryDocument;
use php\classes\ObjectSorting\ObjectSorting;

class ScadFile {

    private $binaryFileContent;
    private $cursor;
    private $docs = array();
    private $headerBody;
    private $docOrder = array();

    private $bytesCountByType = array('S' =>  2, 'L' => 4);
    
    const CORRECT_HEADER_WORD = '*Schema*';
    const FOOTER_VARIABLE_ADDRESS = 8;
    const HEADER_DOCUMENT_ADDRESS = 12;
    const FINAL_DOCUMENT_NUMBER = 0;

    public function __construct($content) {

        $this->binaryFileContent = $content;

        $this->checkHeaderWordCorrect();
        $this->uploadDocuments();
    }

    private function checkHeaderWordCorrect() {
        $this->cursor = 0;
        $headerWord = $this->readPortionFromCursorPosition(strlen(self::CORRECT_HEADER_WORD));
        
        if (strcmp($headerWord, self::CORRECT_HEADER_WORD) != 0) {
            throw new WrongFileFormatException;
        }
    }

    private function uploadDocuments() {
        
        $this->cursor = $this->getFileFooterAddress();
        while ($this->addNewDocumentUsingDescriptionInCursorPosition()) {}
        
        $this->isCursorInTheEndPosition();
        
        $this->readHeaderBody();
        $this->readBodyAndAppendixOfAllDocument();
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
        
        $this->docs[] = new BinaryDocument($number, $address, $bytesCount);
        return TRUE;
    }
    
    private function isCursorInTheEndPosition() {
        if ($this->cursor != strlen($this->binaryFileContent)) {
            throw new WrongFileFormatException;
        }
    }

    private function isNumberOfDocumentFinal($number) {
        return ($number == self::FINAL_DOCUMENT_NUMBER);
    }
    
    public function getContent() {
        return NULL;
    }
    
    private function readHeaderBody() {
        
        $this->cursor = self::HEADER_DOCUMENT_ADDRESS;
        $minDocAddress = $this->getMinDocAddress();
        
        $this->headerBody = $this->readPortionFromCursorPosition($minDocAddress - self::HEADER_DOCUMENT_ADDRESS);
    }
    
    private function getMinDocAddress() {
        $minAddress = $this->getFileFooterAddress();
        
        foreach ($this->docs as $doc) {
            if ($doc->address < $minAddress) {
                $minAddress = $doc->address;
            }
        }
    }
    
    private function readPortionFromCursorPosition($bytesCount) {
        $this->isVariableInsideFile($bytesCount);
        return substr($this->binaryFileContent, $this->cursor, $bytesCount);
    }
    
    private function readBodyAndAppendixOfAllDocument() {
        $docsSortedByAddress = ObjectSorting::sortByProperty($this->docs, 'address');
        
        foreach ($docsSortedByAddress as $doc) {
            $this->docOrder[] = $doc->number;
        }
        
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
        
        $this->isVariableInsideFile($bytesCount);

        $variableBinaryData = substr($this->binaryFileContent, $this->cursor, $bytesCount);
        $unpackedArray = unpack($type, $variableBinaryData);
        
        $this->cursor += $bytesCount;

        return $unpackedArray[1];
    }
    
    private function isVariableInsideFile($bytesCount) {
        if (strlen($this->binaryFileContent) < ($this->cursor + $bytesCount)) {
            throw new WrongFileFormatException;
        }
    }

}
