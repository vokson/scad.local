<?php

/*
  $binarydata = "\x01";
  var_dump(unpack('C', $binarydata));

  $binarydata = "\x0a\x00\x00\x00";
  var_dump(unpack('V', $binarydata));

  $binarydata = "\x6f\x12\x83\x3a";
  var_dump(unpack('f', $binarydata));

  $binarydata = "\x00\x00\x00\x00\x00\x29\xc3\x40";
  var_dump(unpack('d', $binarydata));

  $binarydata = "\x00\x00\x00\x00\x10\xf0\x2d\x41";
  var_dump(unpack('d', $binarydata));
 */

//функция декодирует 8 байтов в число
function bytes2double($number, $MNO, $STEP) {
//    echo 'Number has '.strlen($number).' bytes = '.$number.'<br/>';
    //переворачиваем строку
    $number = strrev($number);
    //создаем массив
    $mas = NULL;
    for ($i = 0; $i < 8; $i++)
        $mas[$i] = sprintf("%08b", ord($number[$i]));
//    var_dump($mas);
    //создаем 64 битную строку
    $s = implode('', $mas);
//    echo 'S = '.$s.'<br/>';
    //разделяем на биты знака, экспоненты и мантиссы
    $znak = substr($s, 0, 1);
//    echo 'ZNAK = '.$znak.'<br/>';
    $exp = substr($s, 1, 11);
//    echo 'EXP = '.bindec($exp).'<br/>';
    $mant = substr($s, 12, 52);
//    echo 'MANT = '.bindec($mant).'<br/>';
    //находим множители
    $a = pow(-1, bindec($znak));
//    echo 'A = '.$a.'<br/>';
    $b = pow(2, bindec($exp) - $STEP - 1023);
//    echo 'B = '.$b.'<br/>';
    $c = 1 + bindec($mant) / pow(2, 52);
//    echo 'C = '.$c.'<br/>';
    //считаем результат
//    echo 'RESULT = '.($a*$b*$c*$MNO).'<br/>';
    return $a * $b * $c * $MNO;
}

//послед-ть байт в целое число без знака, старшие байты в конце
function bytes2un_int($bytes) {
    $uns_int = 0;
    for ($i = 0; $i <= strlen($bytes) - 1; $i++)
        $uns_int += ord($bytes[$i]) * pow(256, $i);
    return $uns_int;
}

/*
 * Распаковывает байты в число u_int(1)
 * 
 * @param string $data Строка байтов
 * return int Распакованное число
 */

function unpackInt_1($data) {
    $array = array_values(unpack('C', $data));
    return $array[0];
}

/*
 * Распаковывает байты в число u_int(2)
 * 
 * @param string $data Строка байтов
 * return int Распакованное число
 */

function unpackInt_2($data) {
    $array = array_values(unpack('v', $data));
    return $array[0];
}

/*
 * Распаковывает байты в число u_int(4)
 * 
 * @param string $data Строка байтов
 * return int Распакованное число
 */

function unpackInt_4($data) {
    $array = array_values(unpack('V', $data));
    return $array[0];
}

/*
 * Распаковывает байты в число float(4)
 * 
 * @param string $data Строка байтов
 * return int Распакованное число
 */

function unpackFloat($data) {
    $array = array_values(unpack('f', $data));
    return $array[0];
}

/*
 * Распаковывает байты в число double(8)
 * 
 * @param string $data Строка байтов
 * return int Распакованное число
 */

function unpackDouble($data) {
    $array = array_values(unpack('d', $data));
    return $array[0];
}

/*
 * Функция сравнения двух документов по bytes_offset
 */
function compareDocByOffset($a, $b) {
    if ($a->byte_offset == $b->byte_offset) {
        return 0;
    }
    return ($a->byte_offset < $b->byte_offset) ? -1 : 1;
}

/*
 * Функция сравнения двух документов по number
 */
function compareDocByNumber($a, $b) {
    if ($a->number == $b->number) {
        return 0;
    }
    return ($a->number < $b->number) ? -1 : 1;
}
