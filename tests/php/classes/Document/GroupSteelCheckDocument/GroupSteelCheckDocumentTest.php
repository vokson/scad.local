<?php

namespace php\classes\Document\GroupSteelCheckDocument;

use php\classes\BinaryDataContainer\WrongDataFormatException;

class GroupSteelCheckDocumentTest extends \PHPUnit_Framework_TestCase {

    const CORRECT_TEST_FILE_NAME = "correctScad21TestFile.SPR";

    private $correctTestFileContent;
    private $correctScadFile;
    
    protected function setUp() {
        $this->wrongFileFormatExceptionClassName = get_class(new WrongDataFormatException);

        $this->correctTestFileContent = $this->getFileContent(self::CORRECT_TEST_FILE_NAME);
        $this->correctScadFile = new GroupSteelCheckDocument($this->correctTestFileContent);
    }

    private function getFileContent($fileName) {
        return file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . $fileName));
    }

    public function testIsCorrectScadFileUploaded() {
        $isObjectCreated = !is_null($this->correctScadFile);
        $this->assertTrue($isObjectCreated);
    }
}
