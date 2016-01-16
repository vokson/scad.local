<?php

namespace php\classes\PropertyContainer;

class PropertyContainerTest extends \PHPUnit_Framework_TestCase
{
    
    const PROPERTY_VALUE = 666;
    
    protected $object;
    
    protected function setUp()
    {
        $this->object = new PropertyContainer;
        $this->object->validProperty = self::PROPERTY_VALUE;
        $this->object->invalidProperty = self::PROPERTY_VALUE;
        unset($this->object->invalidProperty);
    }

    public function testPropertyIsset() {
        $this->assertEquals(TRUE, isset($this->object->validProperty));
    }
    
    public function testPropertyUnset() {
        $this->assertEquals(FALSE, isset($this->object->invalidProperty));
    }
    
    
    public function testPropertySetCorrectly() {
        $this->assertEquals(self::PROPERTY_VALUE, $this->object->validProperty);
    }
}
