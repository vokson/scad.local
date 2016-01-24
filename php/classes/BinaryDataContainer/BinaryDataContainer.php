<?php

namespace php\classes\BinaryDataContainer;

class BinaryDataContainer {

    private $bytesCountByType = array('C' => 1, 'S' => 2, 'L' => 4);
    protected $binaryFileContent;
    protected $cursor;

    public function __construct($content) {
        $this->binaryFileContent = $content;
        $this->cursor = 0;
    }

    public function setCursor($position) {
        $this->cursor = $position;
    }

    public function getCursor() {
        return $this->cursor;
    }

    public function shiftCursor($increment) {
        $this->cursor += $increment;
    }
    
    public function isCursorInTheEndPosition() {
        return $this->getCursor() == strlen($this->binaryFileContent);
    }

    public function readPortionFromCursorPosition($bytesCount) {
        $this->isVariableInsideFile($bytesCount);

        $result = substr($this->binaryFileContent, $this->cursor, $bytesCount);
        $this->cursor += $bytesCount;

        return $result;
    }

    public function readCharArrayUntilZeroByte($shiftCursor = NULL) {

        $nullBytePos = strpos($this->binaryFileContent, "\x00", $this->cursor);
        $length = $nullBytePos - $this->cursor;

        $charArray = substr($this->binaryFileContent, $this->cursor, $length);

        if ($shiftCursor === NULL) {
            $this->cursor += $length;
        } else {
            $this->cursor += $shiftCursor;
        }

        return $charArray;
    }

    /*
     *  MAP FORMAT
     *  (NAME, VARIABLE TYPE, COUNT OF VARIABLES TO READ)
     */

    public function packPortionByMap($array, $map) {

        $binaryData = '';

        foreach ($map as $row) {

            $name = $row[0];
            $type = $row[1];
            $count = $row[2];

            $format = $type . $count;

            if ($type == 'x') {
                $packedVariable = pack($format);
            } else {
                $packedVariable = pack($format, $array[$name]);
            }

            $binaryData .= $packedVariable;
        }

        return $binaryData;
    }

    /*
     *  MAP FORMAT
     *  (NAME, VARIABLE TYPE, COUNT OF VARIABLES TO READ)
     */

    public function unpackPortionByMap($bytesCount, $map) {
        $formatArray = array();

        foreach ($map as $row) {
            $name = $row[0];
            $type = $row[1];
            $count = $row[2];

            $formatArray[] = $type . $count . $name;
        }

        $formatString = implode('/', $formatArray);

        return unpack($formatString, $this->readPortionFromCursorPosition($bytesCount));
    }

    /*
     * Since unpack('Q') is not work on PHP 32-bit,
     * read 32-bit integer and 32-bit zeros
     */

    public function unpackLongValue() {
        $intValue = $this->unpackIntValue();
        $this->unpackIntValue();

        return $intValue;
    }

    public function packLongValue($value) {
        return $this->packIntValue($value) . $this->packIntValue(0);
    }

    public function unpackIntValue() {
        return $this->unpackValue('L');
    }

    public function packIntValue($value) {
        return pack('L', $value);
    }

    public function unpackShortValue() {
        return $this->unpackValue('S');
    }

    public function packShortValue($value) {
        return pack('S', $value);
    }

    public function unpackCharValue() {
        return $this->unpackValue('C');
    }

    public function packCharValue($value) {
        return pack('C', $value);
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
            throw new WrongDataFormatException;
        }
    }

}
