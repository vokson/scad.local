<?php

namespace php\classes\Document\GroupSteelCheckDocument;
use php\classes\ScadFile\ScadFile;
use php\classes\BinaryDataContainer\WrongDataFormatException;
use php\classes\Document\GroupSteelCheckDocument\MissingGroupException;

class GroupSteelCheckDocumentTest extends \PHPUnit_Framework_TestCase {

    const CORRECT_TEST_FILE_NAME = "correctScad21TestFile.SPR";
    const CORRECT_COUNT_OF_GROUPS = 16;
    const INCORRECT_GROUP_NUMBER = 20;
    const CORRECT_GROUP_NUMBER = 1;
    

    private $wrongFileFormatExceptionClassName;
    private $missingGroupExceptionClassName;

    private $correctTestFileContent;
    private $correctScadFile;
    private $correctDocument;
    
    protected function setUp() {
        $this->wrongFileFormatExceptionClassName = get_class(new WrongDataFormatException);
        $this->missingGroupExceptionClassName = get_class(new MissingGroupException);

        $this->correctTestFileContent = $this->getFileContent(self::CORRECT_TEST_FILE_NAME);
        $this->correctScadFile = new ScadFile($this->correctTestFileContent);
        $this->correctDocument = new GroupSteelCheckDocument($this->correctScadFile->getSteelCheckGroupDocument());
    }

    private function getFileContent($fileName) {
        return file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . $fileName));
    }

    public function testIsCorrectDocumentUploaded() {
        $isObjectCreated = !is_null($this->correctDocument);
        $this->assertTrue($isObjectCreated);
    }
    
    public function testGetGroupCount() {
        $this->assertEquals(self::CORRECT_COUNT_OF_GROUPS, $this->correctDocument->getGroupCount());
    }
    
    public function testGetGroupByIncorrectNumber() {
        $this->setExpectedException($this->missingGroupExceptionClassName);
        $this->correctDocument->getGroupByNumber(self::INCORRECT_GROUP_NUMBER);
    }
    
    public function testGetGroupByCorrectNumber() {
        $isObjectCreated = !is_null($this->correctDocument->getGroupByNumber(self::CORRECT_GROUP_NUMBER));
        $this->assertTrue($isObjectCreated);
    }
}
