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
    private $fileFooterAddress;
    private $bytesCountByType = array('S' => 2, 'L' => 4);

    const CORRECT_HEADER_WORD = '*Schema*';
    const FOOTER_VARIABLE_ADDRESS = 8;
    const HEADER_DOCUMENT_ADDRESS = 12;
    const FINAL_DOCUMENT_NUMBER = 0;
    
    const DOC_STEEL_CHECK_GROUP_NUMBER = 28;

    public function __construct($content) {

        $this->binaryFileContent = $content;

        $this->checkHeaderWordCorrect();

        $this->fileFooterAddress = $this->getFileFooterAddress();

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

        $this->cursor = $this->fileFooterAddress;

        while ($this->addNewDocumentUsingDescriptionInCursorPosition()) {
            
        }

        $this->isCursorInTheEndPosition();

        $this->readBodyAndAppendixOfAllDocument();
        $this->readHeaderBody();
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

        $this->docs[$number] = new BinaryDocument($number, $address, $bytesCount);
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

        $content = '';

        $content .= self::CORRECT_HEADER_WORD;
        $content .= $this->packIntValue(0);
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

    private function getContentWithCorrectFooterAddress($data) {
        $address = $this->packIntValue(strlen($data));
        return substr_replace($data, $address, self::FOOTER_VARIABLE_ADDRESS, strlen($address));
    }

    private function getFooter() {
        $footer = '';
        foreach ($this->docs as $doc) {
            $footer .= $this->packShortValue($doc->number);
            $footer .= $this->packLongValue($doc->address);
            $footer .= $this->packLongValue($doc->bytesCount);
        }
        $footer .= $this->packShortValue(0);

        return $footer;
    }
    
    public function getSteelCheckGroupDocument() {
        return NULL;
    }

    private function readHeaderBody() {

        $this->cursor = self::HEADER_DOCUMENT_ADDRESS;
        $this->headerBody = $this->readPortionFromCursorPosition($this->getMinDocAddress() - self::HEADER_DOCUMENT_ADDRESS);
    }

    private function getMinDocAddress() {
        return $this->docs[$this->docOrder[0]]->address;
    }

    private function readPortionFromCursorPosition($bytesCount) {
        $this->isVariableInsideFile($bytesCount);

        $result = substr($this->binaryFileContent, $this->cursor, $bytesCount);
        $this->cursor += $bytesCount;

        return $result;
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

            $this->cursor = $bodyAddress;
            $this->docs[$currentDocNumber]->body = $this->readPortionFromCursorPosition($bodyBytesCount);
            $this->docs[$currentDocNumber]->appendix = $this->readPortionFromCursorPosition($appendixBytesCount);
        }
    }

    private function setDocumentsOrder() {
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

    private function packLongValue($value) {
        return $this->packIntValue($value) . $this->packIntValue(0);
    }

    private function unpackIntValue() {
        return $this->unpackValue('L');
    }

    private function packIntValue($value) {
        return pack('L', $value);
    }

    private function unpackShortValue() {
        return $this->unpackValue('S');
    }

    private function packShortValue($value) {
        return pack('S', $value);
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
