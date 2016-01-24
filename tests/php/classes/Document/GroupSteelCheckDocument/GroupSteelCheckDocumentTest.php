<?php

namespace php\classes\Document\GroupSteelCheckDocument;

use php\classes\ScadFile\ScadFile;
use php\classes\BinaryDataContainer\WrongDataFormatException;
use php\classes\Document\GroupSteelCheckDocument\MissingGroupException;

class GroupSteelCheckDocumentTest extends \PHPUnit_Framework_TestCase {

    const TEST_FILE_NAME = "correctScad21TestFile.SPR";

//    const CORRECT_COUNT_OF_GROUPS = 4;

    private $wrongFileFormatExceptionClassName;
    private $missingGroupExceptionClassName;
    private $testFileContent;
    private $scadFile;
    private $document;
    private $array = array(
        array(
            'name' => 'Group 1',
            'group_type' => 1,
            'member_type' => 0,
            'isMuReg' => 1,
            'isMuUsed' => 0,
            'onlyElastic' => 0,
            'steel' => 'C255',
            'Ry' => 0.0,
            'gamma_c' => 0.95,
            'gamma_n' => 1.1,
            'mu_XZ' => 1.0,
            'mu_XY' => 1.0,
            'FC' => 150.0,
            'FT' => 400.0,
            'BD' => 6.0,
            'length_XZ' => 3.0,
            'length_XY' => 1.5,
            'check_DAL' => 0,
            'check_DTL' => 0,
            'limit_RDAL' => 0.007,
            'limit_RDTL' => 0.007,
            'limit_ADAL' => 0.007,
            'limit_ADTL' => 0.007,
            'addGroup' => 0,
            'list' => array(2, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43)
        ),
        array(
            'name' => 'Элемент 2',
            'group_type' => 0,
            'member_type' => 0,
            'isMuReg' => 1,
            'isMuUsed' => 1,
            'onlyElastic' => 1,
            'steel' => '',
            'Ry' => 999000,
            'gamma_c' => 0.9,
            'gamma_n' => 1.2,
            'mu_XZ' => 3.0,
            'mu_XY' => 6.0,
            'FC' => '180-60a',
            'FT' => 300.0,
            'BD' => 0,
            'length_XZ' => 0,
            'length_XY' => 0,
            'check_DAL' => 1,
            'check_DTL' => 1,
            'limit_RDAL' => 'auto',
            'limit_RDTL' => 0.008,
            'limit_ADAL' => 'no limit',
            'limit_ADTL' => 20,
            'addGroup' => 0,
            'list' => array(92, 93, 94)
        ),
        array(
            'name' => 'Element 3',
            'group_type' => 0,
            'member_type' => 3,
            'isMuReg' => 1,
            'isMuUsed' => 1,
            'onlyElastic' => 0,
            'steel' => 'C235',
            'Ry' => 0,
            'gamma_c' => 0.9,
            'gamma_n' => 1.1,
            'mu_XZ' => 0,
            'mu_XY' => 0,
            'FC' => 0,
            'FT' => 0,
            'BD' => 4.5,
            'length_XZ' => 0,
            'length_XY' => 0,
            'check_DAL' => 1,
            'check_DTL' => 0,
            'limit_RDAL' => 0.007,
            'limit_RDTL' => 0,
            'limit_ADAL' => 23,
            'limit_ADTL' => 0,
            'addGroup' => 1,
            'list' => array(326, 327)
        ),
        array(
            'name' => 'Group 4',
            'group_type' => 1,
            'member_type' => 6,
            'isMuReg' => 0,
            'isMuUsed' => 1,
            'onlyElastic' => 0,
            'steel' => 'ВСт3пс5',
            'Ry' => 0,
            'gamma_c' => 0.5,
            'gamma_n' => 1.5,
            'mu_XZ' => 1.5,
            'mu_XY' => 2.7,
            'FC' => 0,
            'FT' => 0,
            'BD' => 0,
            'length_XZ' => 0,
            'length_XY' => 0,
            'check_DAL' => 0,
            'check_DTL' => 1,
            'limit_RDAL' => 0,
            'limit_RDTL' => 0.007,
            'limit_ADAL' => 0,
            'limit_ADTL' => 16,
            'addGroup' => 1,
            'list' => array(104, 333)
        )
    );

    protected function setUp() {
        $this->wrongFileFormatExceptionClassName = get_class(new WrongDataFormatException);
        $this->missingGroupExceptionClassName = get_class(new MissingGroupException);

        $this->testFileContent = $this->getFileContent(self::TEST_FILE_NAME);
        $this->scadFile = new ScadFile($this->testFileContent);
        $this->document = new GroupSteelCheckDocument();
        $this->content = $this->scadFile->getSteelCheckGroupDocument();
    }

    private function getFileContent($fileName) {
        return file_get_contents(realpath(__DIR__ . DIRECTORY_SEPARATOR . $fileName));
    }

    public function testIsObjectCreated() {
        $isObjectCreated = !is_null($this->document);
        $this->assertTrue($isObjectCreated);
    }

//
//    public function testGroupCount() {
//        $array = $this->document->binaryDataToArray($this->content);
//        $this->assertEquals(self::CORRECT_COUNT_OF_GROUPS, count($array));
//    }

    public function testBinaryDataToArray() {
        $resultArray = $this->document->binaryDataToArray($this->content);
        var_dump($resultArray);

        for ($i = 0; $i < count($this->array); $i++) {
            
            foreach ($this->array[$i] as $key => $value) {
                $this->assertEquals($value, $resultArray[$i][$key], '', 0.01);
            }
        }
    }

//    public function testArrayToBinaryData() {
//        $resultData = $this->document->arrayToBinaryData($this->array);
//        $this->assertEquals($this->content, $resultData);
//    }
}
