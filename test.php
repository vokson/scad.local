<?php
class SampleClass {
    public $group, $count;
}

$binaryData = "AB\x05\x00\x00\x00BA\x1d\x00\x00\x00";
$format = array(
    array('', 'x', 2),
    array('group', 'I', 1),
    array('', 'x', 2),
    array('count', 'I', 1)
);

$formatArray = array();
foreach ($format as $e) {
    $formatArray[] = $e[1] . $e[2] . $e[0];
}

$formatString = implode ('/', $formatArray);

echo "FORMAT = $formatString<br/>";

$array = unpack($formatString, $binaryData);
var_dump($array);

$object = new SampleClass();
foreach($array as $key => $value) {
    $object->$key = $value;
}

var_dump($object);
