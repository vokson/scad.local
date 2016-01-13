<?php

namespace php\classes\ScadFile;

class ScadFile {

    private $binaryFileContent;
//    private $fileMap = array(
//      'footerAddress' =>  
//    );
    private $footerVariableAddress = 8;
    private $footerVariableBytesCount = 4;

    public function __construct($content) {
        $this->binaryFileContent = $content;
    }

    public function isScadFile() {
        
    }

    public function getFileFooterAddress() {
         if ( strlen($this->binaryFileContent) < ($this->footerVariableAddress + $this->footerVariableBytesCount)) {
            throw new \Exception("File's footer address unpack error");
        }
        
        $variableBinaryData = substr($this->binaryFileContent, $this->footerVariableAddress, $this->footerVariableBytesCount);
        $unpackedArray = unpack('I', $variableBinaryData);

        return $unpackedArray[1];
    }

}
