<?php

require_once dirname(__FILE__) . '/../../../php/classes/class_MemberGroup.php';

/**
 * Test class for MemberGroup.
 * Generated by PHPUnit on 2011-08-28 at 15:18:44.
 */
class MemberGroupTest extends PHPUnit_Framework_TestCase {

    /**
     * @var MemberGroup
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new MemberGroup;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    public function testGet_1() {
        $this->object->get('2 1 Name="+4.000 - B1"  : 29 30');
        $this->assertEquals(2, $this->object->appointment);
        $this->assertEquals(1, $this->object->type);
        $this->assertEquals('+4.000 - B1', $this->object->name);
        $this->assertEquals(array(29,30), $this->object->list);
    }

    public function testGet_2() {
        $this->object->get(' 1  2 Name="Group plastina"    :  314    ');
        $this->assertEquals(1, $this->object->appointment);
        $this->assertEquals(2, $this->object->type);
        $this->assertEquals('Group plastina', $this->object->name);
        $this->assertEquals(array(314), $this->object->list);
    }

    public function testSet_1() {
        $this->object->appointment = 1;
        $this->object->type = 2;
        $this->object->name = 'Group plastina';
        $this->object->list = array(314);
        $this->assertEquals('1 2 Name="Group plastina" : 314',  $this->object->set());
    }

    public function testSet_2() {
        $this->object->appointment = 2;
        $this->object->type = 1;
        $this->object->name = '+4.000 - B1';
        $this->object->list = array(29,30);
        $this->assertEquals('2 1 Name="+4.000 - B1" : 29 30',  $this->object->set());
    }

}

?>
