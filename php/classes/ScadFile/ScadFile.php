<?php

namespace php\classes\ScadFile;

use php\classes\BinaryDataContainer\BinaryDataContainer;
use php\classes\BinaryDocument\BinaryDocument;
use php\classes\ObjectSorting\ObjectSorting;

class ScadFile {
    
    const CORRECT_HEADER_WORD = '*Schema*';
    
    const FOOTER_VARIABLE_ADDRESS = 8;
    const HEADER_DOCUMENT_ADDRESS = 12;
    
    const FINAL_DOCUMENT_NUMBER = 0;
    const DOC_STEEL_CHECK_GROUP_NUMBER = 28;

    private $binaryDataContainer;
    private $docs = array();
    private $docOrder = array();
    private $headerBody;
    private $fileFooterAddress;

    public function __construct($content) {
        
        $this->binaryDataContainer = new BinaryDataContainer($content);

        $this->checkHeaderWordCorrect();

        $this->fileFooterAddress = $this->getFileFooterAddress();

        $this->uploadDocuments();
    }
    
    public function getFile() {

        $content = '';

        $content .= self::CORRECT_HEADER_WORD;
        $content .= $this->binaryDataContainer->packIntValue(0);
        $content .= $this->headerBody;

        for ($i = 0; $i < count($this->docOrder); $i++) {
            $docNumber = $this->docOrder[$i];

            $this->docs[$docNumber]->address = strlen($content);
            $this->docs[$docNumber]->bytesCount = strlen($this->docs[$docNumber]->body);
            $content .= $this->docs[$docNumber]->body;
            $content .= $this->docs[$docNumber]->appendix;
        }

        return $this->getContentWithCorrectFooterAddress($content) . $this->getFooter();
    }

    public function getSteelCheckGroupDocument() {
        return $this->getDocument(self::DOC_STEEL_CHECK_GROUP_NUMBER);
    }
    
    public function setSteelCheckGroupDocument($data) {
        $this->setDocument(self::DOC_STEEL_CHECK_GROUP_NUMBER, $data);
    }
    
    private function checkHeaderWordCorrect() {
        $this->binaryDataContainer->setCursor(0);
        $headerWord = $this->binaryDataContainer->readPortionFromCursorPosition(strlen(self::CORRECT_HEADER_WORD));

        if (strcmp($headerWord, self::CORRECT_HEADER_WORD) != 0) {
            throw new WrongFileFormatException;
        }
    }
    
    private function getFileFooterAddress() {
        $this->binaryDataContainer->setCursor(self::FOOTER_VARIABLE_ADDRESS);
        return $this->binaryDataContainer->unpackIntValue();
    }
    
    private function uploadDocuments() {

        $this->binaryDataContainer->setCursor($this->fileFooterAddress);

        while ($this->addNewDocumentUsingDescriptionInCursorPosition()) {
            
        }

        $this->isCursorInTheEndPosition();

        $this->readBodyAndAppendixOfAllDocument();
        $this->readHeaderBody();
    }
    
    private function getContentWithCorrectFooterAddress($data) {
        $address = $this->binaryDataContainer->packIntValue(strlen($data));
        return substr_replace($data, $address, self::FOOTER_VARIABLE_ADDRESS, strlen($address));
    }
    
    private function getDocument($number) {
        if (isset($this->docs[$number])) {
            return $this->docs[$number]->body;
        } else {
            throw new MissingDocumentException;
        }
    }

    private function setDocument($number, $data) {
        if (isset($this->docs[$number])) {
           $this->docs[$number]->body = $data;
        } else {
            throw new MissingDocumentException;
        }
    }

    private function addNewDocumentUsingDescriptionInCursorPosition() {

        $number = $this->binaryDataContainer->unpackShortValue();

        if ($this->isNumberOfDocumentFinal($number)) {
            return FALSE;
        }

        $address = $this->binaryDataContainer->unpackLongValue();
        $bytesCount = $this->binaryDataContainer->unpackLongValue();

        $this->docs[$number] = new BinaryDocument($number, $address, $bytesCount);
        return TRUE;
    }
    
    private function getFooter() {
        $footer = '';
        foreach ($this->docs as $doc) {
            $footer .= $this->binaryDataContainer->packShortValue($doc->number);
            $footer .= $this->binaryDataContainer->packLongValue($doc->address);
            $footer .= $this->binaryDataContainer->packLongValue($doc->bytesCount);
        }
        $footer .= $this->binaryDataContainer->packShortValue(0);

        return $footer;
    }
    
    private function isCursorInTheEndPosition() {
        if ($this->binaryDataContainer->isCursorInTheEndPosition() === FALSE) {
            throw new WrongFileFormatException;
        }
    }
    
    private function readBodyAndAppendixOfAllDocument() {

        $this->setDocumentsOrder();

        for ($i = 0; $i < count($this->docOrder); $i++) {
            
            $currentDocNumber = $this->docOrder[$i];
            
            $bodyAddress = $this->docs[$currentDocNumber]->address;
            $bodyBytesCount = $this->docs[$currentDocNumber]->bytesCount;

            if ($i + 1  < count($this->docOrder)) {
                $nextDocNumber = $this->docOrder[$i + 1];
                $nextDocAddress = $this->docs[$nextDocNumber]->address;
            } else {
                $nextDocAddress = $this->fileFooterAddress;
            }

            $appendixAddress = $bodyAddress + $bodyBytesCount;
            $appendixBytesCount = $nextDocAddress - $appendixAddress;

            $this->binaryDataContainer->setCursor($bodyAddress);
            $this->docs[$currentDocNumber]->body = $this->binaryDataContainer->readPortionFromCursorPosition($bodyBytesCount);
            $this->docs[$currentDocNumber]->appendix = $this->binaryDataContainer->readPortionFromCursorPosition($appendixBytesCount);
        }
    }
    
    private function readHeaderBody() {

        $this->binaryDataContainer->setCursor(self::HEADER_DOCUMENT_ADDRESS);
        $this->headerBody = $this->binaryDataContainer->readPortionFromCursorPosition($this->getMinDocAddress() - self::HEADER_DOCUMENT_ADDRESS);
    }

    private function isNumberOfDocumentFinal($number) {
        return ($number == self::FINAL_DOCUMENT_NUMBER);
    }

    private function setDocumentsOrder() {
        $docsSortedByAddress = ObjectSorting::sortByProperty($this->docs, 'address');

        foreach ($docsSortedByAddress as $doc) {
            $this->docOrder[] = $doc->number;
        }
    }

    private function getMinDocAddress() {
        return $this->docs[$this->docOrder[0]]->address;
    }
    
 
}
