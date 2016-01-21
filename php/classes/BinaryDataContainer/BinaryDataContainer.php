<?php

namespace php\classes\BinaryDataContainer;

class BinaryDataContainer {

    private $bytesCountByType = array('S' => 2, 'L' => 4);
    protected $binaryFileContent;
    protected $cursor;

    public function __construct($content) {
        $this->binaryFileContent = $content;
        $this->cursor = 0;
    }

    protected function readPortionFromCursorPosition($bytesCount) {
        $this->isVariableInsideFile($bytesCount);

        $result = substr($this->binaryFileContent, $this->cursor, $bytesCount);
        $this->cursor += $bytesCount;

        return $result;
    }

    /*
     * Since unpack('Q') is not work on PHP 32-bit,
     * read 32-bit integer and 32-bit zeros
     */

    protected function unpackLongValue() {
        $intValue = $this->unpackIntValue();
        $zeroValue = $this->unpackIntValue();

        return $intValue;
    }

    protected function packLongValue($value) {
        return $this->packIntValue($value) . $this->packIntValue(0);
    }

    protected function unpackIntValue() {
        return $this->unpackValue('L');
    }

    protected function packIntValue($value) {
        return pack('L', $value);
    }

    protected function unpackShortValue() {
        return $this->unpackValue('S');
    }

    protected function packShortValue($value) {
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

    protected function isVariableInsideFile($bytesCount) {
        if (strlen($this->binaryFileContent) < ($this->cursor + $bytesCount)) {
            throw new WrongDataFormatException;
        }
    }

}
