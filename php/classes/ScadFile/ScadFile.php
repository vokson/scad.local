<?php

namespace php\classes\ScadFile;

class ScadFile {

    private $binaryFileContent;
    private $docs = array();
//    private $fileMap = array(
//      'footerAddress' =>  
//    );
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
        $offset = $this->getFileFooterAddress();
        
        $isFinalDocument = FALSE;
        while ($isFinalDocument === FALSE) {
            $docNumber = $this->unpackShortValue($offset);
            $offset += 2;
            
            $isFinalDocument = $this->isNumberOfDocumentFinal($docNumber);
            
            if ($isFinalDocument === FALSE) {
                $docAddress = $this->unpackIntegerValue($offset);
                $offset += 8;
                $docBytesCount = $this->unpackIntegerValue($offset);
                $offset += 8;
                
                $this->docs[] = new \php\classes\BinaryDocument\BinaryDocument($docNumber, $docAddress, $docBytesCount);
            } else {
                if ($offset != strlen($this->binaryFileContent)) {
                    throw new WrongFileFormatException;
                }
            }
        }
    }
    
    private function isNumberOfDocumentFinal($number) {
        return ($number == 0);
    }

    private function getFileFooterAddress() {
        return $this->unpackIntegerValue(self::FOOTER_VARIABLE_ADDRESS);
    }
    
    private function unpackIntegerValue($address) {
        return $this->unpackValue($address, 4, 'I');
    }
    
    private function unpackShortValue($address) {
        return $this->unpackValue($address, 2, 'S');
    }
    
    private function unpackValue($address, $bytesCount, $type) {
        if ( strlen($this->binaryFileContent) < ($address + $bytesCount)) {
            throw new WrongFileFormatException;
        }
        
        $variableBinaryData = substr($this->binaryFileContent, $address, $bytesCount);
        $unpackedArray = unpack($type, $variableBinaryData);

        return $unpackedArray[1];
    }

}
