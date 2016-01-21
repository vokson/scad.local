<?php

namespace php\classes\Document\GroupSteelCheckDocument;

use php\classes\BinaryDataContainer\BinaryDataContainer;

class GroupSteelCheckDocument extends BinaryDataContainer {
    
    const GROUP_COUNT_ADDRESS = 1;
    
    private $groupCount;
    private $groups = array();

    public function __construct($content) {
        parent::__construct($content);
        
        $this->setGroupCount();
    }
    
    public function getGroupCount() {
        return $this->groupCount;
    }
    
    private function setGroupCount() {
        $this->cursor = self::GROUP_COUNT_ADDRESS;
        $this->groupCount = $this->unpackIntValue();
    }
    
    public function getGroupByNumber($number) {
        if (!isset($this->groups[$number])) {
            throw new MissingGroupException;
        }
    }
}
