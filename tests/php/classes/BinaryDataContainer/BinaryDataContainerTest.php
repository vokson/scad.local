<?php

namespace php\classes\BinaryDataContainer;

class BinaryDataContainerTest extends \PHPUnit_Framework_TestCase {

    const DATA = "\x0a\x39\x30\xd2\x02\x96\x49\x00\x00\x00\x00\xb1\x68\xde\x3a\x00\x00\x00\x00";
    
    const PORTION_CORRECT_ADDRESS = 3;
    const PORTION_INCORRECT_ADDRESS = 17;
    const PORTION_COUNT = 5;
    const PORTION = "\xd2\x02\x96\x49\x00";
    
    const CHAR_ARRAY_ADDRESS = 0;
    const CHAR_ARRAY_COUNT = 7;
    const CHAR_ARRAY = "\x0a\x39\x30\xd2\x02\x96\x49";
    
    const CHAR_ADDRESS = 0;
    const CHAR_VALUE = 10;
    const SHORT_ADDRESS = 1;
    const SHORT_VALUE = 12345;
    const INT_ADDRESS = 3;
    const INT_VALUE = 1234567890;
    const LONG_ADDRESS = 11;
    const LONG_VALUE = 987654321;

    private $wrongDataFormatExceptionClassName;
    private $portionMap = [
        ['char', 'C', 1],
        ['short', 'S', 1], 
        ['int', 'L', 1],
        ['', 'x', 4],
        ['long', 'L', 1],
        ['', 'x', 4]
    ];
    
    private $portionArray = [
        'char' => 10,
        'short' => 12345, 
        'int' => 1234567890,
        'long' => 987654321
    ];
    
    protected $object;

    protected function setUp() {
        $this->wrongDataFormatExceptionClassName = get_class(new WrongDataFormatException);
        $this->object = new BinaryDataContainer(self::DATA);
    }

    public function testIsObjectCreated() {
        $isObjectCreated = !is_null($this->object);
        $this->assertTrue($isObjectCreated);
    }

    public function testSetCursor() {
        $this->object->setCursor(self::INT_ADDRESS);
        $this->assertEquals(self::INT_ADDRESS, $this->object->getCursor());
    }

    public function testShiftCursor() {
        $this->object->setCursor(self::INT_ADDRESS);
        $this->object->shiftCursor(2);
        $this->assertEquals(self::INT_ADDRESS + 2, $this->object->getCursor());
    }
    
    public function testIsCursorInTheEndPosition() {
        $this->object->setCursor(strlen(self::DATA));
        $this->assertTrue($this->object->isCursorInTheEndPosition());
    }

    public function testReadPortionFromCorrectPosition() {
        $this->object->setCursor(self::PORTION_CORRECT_ADDRESS);
        $this->assertEquals(self::PORTION, $this->object->readPortionFromCursorPosition(self::PORTION_COUNT));
    }

    public function testReadPortionFromIncorrectPosition() {
        $this->setExpectedException($this->wrongDataFormatExceptionClassName);

        $this->object->setCursor(self::PORTION_INCORRECT_ADDRESS);
        $this->object->readPortionFromCursorPosition(self::PORTION_COUNT);
    }

    public function testReadCharArrayUntilZeroByte() {
        $this->object->setCursor(self::CHAR_ARRAY_ADDRESS);
        $this->assertEquals(self::CHAR_ARRAY, $this->object->readCharArrayUntilZeroByte());
        $this->assertEquals(self::CHAR_ARRAY_ADDRESS + self::CHAR_ARRAY_COUNT, $this->object->getCursor());
    }
    
    public function testReadCharArrayUntilZeroByteWithOffset() {
        $this->object->setCursor(self::CHAR_ARRAY_ADDRESS);
        $this->assertEquals(self::CHAR_ARRAY, $this->object->readCharArrayUntilZeroByte(2));
        $this->assertEquals(self::CHAR_ARRAY_ADDRESS + 2, $this->object->getCursor());
    }

    public function testPackPortionByMap() {
        $this->assertEquals(self::DATA,
                $this->object->packPortionByMap($this->portionArray, $this->portionMap));
    }

    public function testUnpackPortionByMap() {
        $this->object->setCursor(0);
        $result = $this->object->unpackPortionByMap(strlen(self::DATA), $this->portionMap);
        
        foreach ($this->portionArray as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testUnpackLongValue() {
        $this->object->setCursor(self::LONG_ADDRESS);
        $this->assertEquals(self::LONG_VALUE, $this->object->unpackLongValue());
    }

    public function testPackLongValue() {
        $this->assertEquals(substr(self::DATA, self::LONG_ADDRESS, 8),
                $this->object->packLongValue(self::LONG_VALUE));
    }

    public function testUnpackIntValue() {
        $this->object->setCursor(self::INT_ADDRESS);
        $this->assertEquals(self::INT_VALUE, $this->object->unpackIntValue());
    }

    public function testPackIntValue() {
        $this->assertEquals(substr(self::DATA, self::INT_ADDRESS, 4),
                $this->object->packIntValue(self::INT_VALUE));
    }

    public function testUnpackShortValue() {
        $this->object->setCursor(self::SHORT_ADDRESS);
        $this->assertEquals(self::SHORT_VALUE, $this->object->unpackShortValue());
    }

    public function testPackShortValue() {
        $this->assertEquals(substr(self::DATA, self::SHORT_ADDRESS, 2),
                $this->object->packShortValue(self::SHORT_VALUE));
    }

    public function testUnpackCharValue() {
        $this->object->setCursor(self::CHAR_ADDRESS);
        $this->assertEquals(self::CHAR_VALUE, $this->object->unpackCharValue());
    }

    public function testPackCharValue() {
         $this->assertEquals(substr(self::DATA, self::CHAR_ADDRESS, 1),
                $this->object->packCharValue(self::CHAR_VALUE));
    }

    /**
     * @covers php\classes\BinaryDataContainer\BinaryDataContainer::unpackValue
     * @todo   Implement testUnpackValue().
     */
    public function testUnpackValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers php\classes\BinaryDataContainer\BinaryDataContainer::isVariableInsideFile
     * @todo   Implement testIsVariableInsideFile().
     */
    public function testIsVariableInsideFile() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
