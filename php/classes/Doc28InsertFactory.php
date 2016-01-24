<?php

namespace php\classes;

use php\classes\InsertFactory;

/**
 * Export DOC 28 object into database
 *

 */
class Doc28InsertFactory extends InsertFactory {
    /*
     * Insert object
     * 
     * @param MemberGroupSteel21 $obj Object to be exported
     * 
     * @return string Database query
     */

    public function newInsert($obj) {

        // Get properties
        $values = $obj->getArray();
//        $values['steel'] = Utils::databaseEncoding($values['steel'], TRUE);
//        $values['name'] = Utils::databaseEncoding($values['name'], TRUE);
        $values['list'] = Utils::databaseList($values['list'], TRUE);

        return $this->buildStatement(Utils::nameOfDoc28Table(), $values);
    }

}
