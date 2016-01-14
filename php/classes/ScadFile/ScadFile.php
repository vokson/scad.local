<?php

namespace php\classes\ScadFile;

class ScadFile {

    private $binaryFileContent;
//    private $fileMap = array(
//      'footerAddress' =>  
//    );
    const CORRECT_HEADER_WORD = '*Schema*';
//    private $footerVariableAddress = 8;
//    private $footerVariableBytesCount = 4;

    public function __construct($content) {
        if (!$this->isHeaderWordCorrect($content)) {
            throw new WrongFileFormatException;
        }
        $this->binaryFileContent = $content;
    }

    private function isHeaderWordCorrect($content) {
        $headerWord = substr($content, 0, strlen(self::CORRECT_HEADER_WORD));
        return (strcmp($headerWord, self::CORRECT_HEADER_WORD) == 0);
    }

//    public function getFileFooterAddress() {
//         if ( strlen($this->binaryFileContent) < ($this->footerVariableAddress + $this->footerVariableBytesCount)) {
//            throw new \Exception("File's footer address unpack error");
//        }
//        
//        $variableBinaryData = substr($this->binaryFileContent, $this->footerVariableAddress, $this->footerVariableBytesCount);
//        $unpackedArray = unpack('I', $variableBinaryData);
//
//        return $unpackedArray[1];
//    }

}
