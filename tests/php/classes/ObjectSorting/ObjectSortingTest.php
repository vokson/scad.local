<?php
namespace php\classes\ObjectSorting;

class ObjectSortingTest extends \PHPUnit_Framework_TestCase
{

    private $invalidPropertyExceptionClassName;
    
    protected function setUp()
    {
        $this->invalidPropertyExceptionClassName = get_class(new InvalidPropertyException);
    }
    
    public function testSortByPropertySameClassObjects()
    {
        $objects = array();
        
        $objects[] = new A(2);
        $objects[] = new A(1);
        $objects[] = new A(0);
        
        
        $sortedObjects = ObjectSorting::sortByProperty($objects, 'validProperty');
        
        $this->assertEquals($sortedObjects[0]->validProperty, 0);
        $this->assertEquals($sortedObjects[1]->validProperty, 1);
        $this->assertEquals($sortedObjects[2]->validProperty, 2);
    }
    
    public function testSortByPropertyDifferentClassObjects()
    {
        $objects = array();
        
        $objects[] = new A(2);
        $objects[] = new A(1);
        $objects[] = new B(0);
        
        
        $sortedObjects = ObjectSorting::sortByProperty($objects, 'validProperty');
        
        $this->assertEquals($sortedObjects[0]->validProperty, 0);
        $this->assertEquals($sortedObjects[1]->validProperty, 1);
        $this->assertEquals($sortedObjects[2]->validProperty, 2);
    }
    
    public function testSortByInvalidProperty()
    {
        $this->setExpectedException($this->invalidPropertyExceptionClassName);
        
        $objects = array();
        
        $objects[] = new A(2);
        $objects[] = new A(1);
        $objects[] = new C(0);
        
        ObjectSorting::sortByProperty($objects, 'validProperty');
    }
}

class A {
    public $validProperty;
    
    public function __construct($value) {
        $this->validProperty = $value;
    }
}

class B {
    public $validProperty;
    
    public function __construct($value) {
        $this->validProperty = $value;
    }
}

class C {
    public $invalidProperty;
    
    public function __construct($value) {
        $this->invalidProperty = $value;
    }
}
