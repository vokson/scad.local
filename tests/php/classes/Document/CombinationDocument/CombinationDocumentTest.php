<?php

namespace php\classes\Document\CombinationDocument;

use php\classes\BinaryDataContainer\WrongDataFormatException;

class CombinationDocumentTest extends \PHPUnit_Framework_TestCase {

    const CORRECT_FILE_NAME = "CorrectCombinationDocument";
    const INCREASED_SIZE_FILE_NAME = "IncreasedCombinationDocument";
    const DECREASED_SIZE_FILE_NAME = "DecreasedCombinationDocument";

    private $wrongDataFormatExceptionClassName;
    private $document;
    private $correctContent;
    private $increasedContent;
    private $decreasedContent;
    
    private $array = [
        [1.11, 0, -2.22, 0, 3.33],
        [0, 4.44, 0, -5.55, 0, 6.66]
    ];

    protected function setUp() {
        $this->wrongDataFormatExceptionClassName = get_class(new WrongDataFormatException);

        $this->correctContent = $this->getFileContent(self::CORRECT_FILE_NAME);
        $this->increasedContent = $this->getFileContent(self::INCREASED_SIZE_FILE_NAME);
        $this->decreasedContent = $this->getFileContent(self::DECREASED_SIZE_FILE_NAME);

        $this->document = new CombinationDocument();
    }

    private function getFileContent($fileName) {
        return file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . $fileName));
    }

    public function testIsObjectCreated() {
        $isObjectCreated = !is_null($this->document);
        $this->assertTrue($isObjectCreated);
    }

    public function testBinaryDataToArrayForCorrectFile() {
        $resultArray = $this->document->binaryDataToArray($this->correctContent);

        for ($i = 0; $i < count($this->array); $i++) {

            foreach ($this->array[$i] as $key => $value) {
                $this->assertEquals($value, $resultArray[$i][$key], '', 0.01);
            }
        }
    }
    
    public function testBinaryDataToArrayForIncreasedFile() {
        $this->setExpectedException($this->wrongDataFormatExceptionClassName);
        $this->document->binaryDataToArray($this->increasedContent);
    }
    
    public function testBinaryDataToArrayForDecreasedFile() {
        $this->setExpectedException($this->wrongDataFormatExceptionClassName);
        $this->document->binaryDataToArray($this->decreasedContent);
    }

    public function testArrayToBinaryData() {
        $resultData = $this->document->arrayToBinaryData($this->array);
        $this->assertEquals($this->correctContent, $resultData);
    }

}
