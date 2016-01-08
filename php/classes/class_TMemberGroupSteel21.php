<?php

class TMemberGroupSteel21 {

    private $databaseAction = [
        'steel' => 'databaseEncoding',
        'name' => 'databaseEncoding',
        'list' => 'databaseList'
    ];

    /*
     * Read Document No.28. Upload it into database.
     *
     * @param string $data Binary data
     */

    function read($data) {

        // Курсор для чтения строки
        $pos = 0;

        // Пропускаем неизвестный символ
        $pos += 1;

        // Кол-во групп
        list(, $count) = unpack("I", substr($data, $pos, 4));
        $pos += 4;

        // Пропускаем кол-во байт в блоке list
        $pos += 4;

        // Массив описаний групп
        $groups = array();

        //Читаем description блок каждой группы блоками по 329 байт
        for ($i = 0; $i < $count; $i++) {
            $group = new MemberGroupSteel21();
            // Читаем 
            $group->readSingleDescriptionBlock($data, $pos);

            $groups[$i] = $group;
        }

        //Читаем list блок каждой группы
        for ($i = 0; $i < $count; $i++) {
            // Читаем
            $groups[$i]->readSingleListBlock($data, $pos);
        }

        // Отправляем группы в базу данных
        $this->writeObjectsToDatabase($groups);
    }

    

    /*
     * Write steel groups into database
     * 
     * @param MemberGroupSteel21[] $objects Group's objects
     */

    private function writeObjectsToDatabase($objects) {
        mysql_query("TRUNCATE TABLE " . member_group_for_steel);

        foreach ($objects as $object) {
            $queryPropertyArray = array();

            $properties = get_object_vars($object);
            foreach ($properties as $key => $value) {
                // If there is database action for the property
                if (isset($this->databaseAction[$key])) {
                    $function = $this->databaseAction[$key];
                    $value = $this->$function($value, TRUE);
                }
                // Add to query array
                $queryPropertyArray[] = "$key = '$value'";
            }

            $query = "INSERT IGNORE INTO " . member_group_for_steel . " SET " .
                    implode(',', $queryPropertyArray);

//        echo $query . "<br/>";
            mysql_query($query);
        }
    }
    
    /*
     * Read steel groups from database
     * 
     * @return MemberGroupSteel21[] Group's objects
     */

    private function readObjectsFromDatabase() {
        
        mysql_query("SELECT * FROM " . member_group_for_steel);
        
        switch (mysql_errno()) {
            case 1146:
                echo "<b>Table " . member_group_for_steel . " doesn't exist. Please create DB.</b><br>";
                break;
            default:
                if (mysql_errno() > 0)
                    echo mysql_errno() . '  ' . mysql_error() . '<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    //количество групп
                    $s .= pack('V', mysql_num_rows($result));
                    while ($row = mysql_fetch_object($result, 'MemberGroupSteel11')) {
                        $row->list = explode(' ', $row->list);
                        $s .= $row->set_to_spr();
                    }
                }
        }

        foreach ($objects as $object) {
            $queryPropertyArray = array();

            $properties = get_object_vars($object);
            foreach ($properties as $key => $value) {
                // If there is database action for the property
                if (isset($this->databaseAction[$key])) {
                    $function = $this->databaseAction[$key];
                    $value = $this->$function($value, TRUE);
                }
                // Add to query array
                $queryPropertyArray[] = "$key = '$value'";
            }

            $query = "INSERT IGNORE INTO " . member_group_for_steel . " SET " .
                    implode(',', $queryPropertyArray);

//        echo $query . "<br/>";
            mysql_query($query);
        }
    }

    // создает документ с группами для подбора стали
    // SCAD в файле SPR
    function set_to_scad_spr() {
        $s = '';
        //формируем начало 28 документа
        //класс стали - 80 байт
        $s .= pack('a80', 'C255');
        //сопротивление стали
        $s .= pack('d', 240.26292);
        //0.95 - неизвестно что
        $s .= pack('d', 0.95);
        //gamma_C
        $s .= pack('d', 0.95);
        //гибкость
        $s .= pack('d', 400);
        //нулевой байт
        $s .= pack('a1', '');

        $sql = "SELECT * FROM " . member_group_for_steel;
        mysql_query($sql);
        switch (mysql_errno()) {
            case 1146: echo "<b>Table " . member_group_for_steel . " doesn't exist. Please create DB.</b><br>";
                break;
            default:
                if (mysql_errno() > 0)
                    echo mysql_errno() . '  ' . mysql_error() . '<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    //количество групп
                    $s .= pack('V', mysql_num_rows($result));
                    while ($row = mysql_fetch_object($result, 'MemberGroupSteel11')) {
                        $row->list = explode(' ', $row->list);
                        $s .= $row->set_to_spr();
                    }
                }
        }
        return $s;
    }

}
