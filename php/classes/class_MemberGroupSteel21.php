<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "class_MemberList.php";

class MemberGroupSteel21 extends MemberList {

    public $steel; // Сталь

   private $descriptionBlockMap = [
        ['group_type', 'C', 1], // 0 - констр эл-т, 1 - группа эл-т
        ['member_type', 'C', 1], // Тип конструктивного элемента (0-6)
        ['', 'x', 3], // Пустые байты
        ['isMuReg', 'C', 1], // 0 - нет, 1 - да
        ['isMuUsed', 'C', 1], // 0 - нет, 1 - да
        ['onlyElastic', 'C', 1], // 0 - нет, 1 - да
        ['Ry', 'd', 1], // Сопротивление стали в Н/мм2
        ['gamma_n', 'd', 1], // Гамма N
        ['gamma_c', 'd', 1], // Гамма С
        ['', 'x', 8], // Пустые байты
        ['mu_XZ', 'd', 1], //Коэффициент расчетной длины в пл-ти XoZ
        ['mu_XY', 'd', 1], //Коэффициент расчетной длины в пл-ти XoY
        ['FC', 'd', 1], // Гибкость сжатых элементов
        ['FT', 'd', 1], // Гибкость растянутых элементов
        ['BD', 'd', 1], // Расстояние между точками раскрепления
        ['', 'x', 16], // Пустые байты
        ['length_XZ', 'd', 1], // Расчетная длина в пл-ти XoZ
        ['length_XY', 'd', 1], // Расчетная длина в пл-ти XoY
        ['noname1', 'd', 1], // Неизвестно
        ['noname2', 'd', 1], // Неизвестно
        ['', 'x', 8], // Пустые байты
        ['check_DAL', 'C', 1], // Проверять перемещения от всех нагрузок 0 - нет, 1 - да
        ['check_DTL', 'C', 1], // Проверять перемещения от временных нагрузок 0 - нет, 1 - да
        ['', 'x', 32], // Пустые байты
        ['limit_RDAL', 'f', 1], // Предельные относительные перемещения от всех нагрузок
        ['limit_RDTL', 'f', 1], // Предельные относительные перемещения от временных нагрузок
        ['', 'x', 22], // Пустые байты
        ['addGroup', 'C', 1], // 0 - нет, 1 - да
        ['', 'x', 40], // Пустые байты
        ['limit_ADAL', 'f', 1], // Предельные абсолютные перемещения от всех нагрузок
        ['limit_ADTL', 'f', 1], // Предельные абсолютные перемещения от временных нагрузок
    ];

    public $name; //имя
    public $list = array(); // список элементов

    /*
     * Read single description block
     * 
     * @param string $data Binary data
     * @param int $pos Position of cursost in data
     */

    function readSingleDescriptionBlock($data, &$pos) {

        // класс стали
        $this->steel = $this->readCharArray($data, $pos);
        $pos += 80 - strlen($this->steel);

        // Data Reader
        $dataReader = new DataReader();

        // Add format to data reader
        foreach ($this->descriptionBlockMap as $e) {
            $dataReader->addFormat($e[1], $e[0], $e[2]);
        }

        $propertyArray = $dataReader->unpack(substr($data, $pos, 249));

        // Apply properties into class
        foreach ($propertyArray as $key => $value) {
            $this->{$key} = $value;
        }

        $pos += 249;
    }

    /*
     * Read single list block
     * 
     * @param string $data Binary data
     * @param int $pos Position of cursor in data
     */

    function readSingleListBlock($data, &$pos) {
        // Имя группы
        $this->name = $this->readCharArray($data, $pos);

        // Кол-во элементов в группе
        list(, $count) = unpack("I", substr($data, $pos, 4));
        $pos += 4;

        foreach (unpack("I$count", substr($data, $pos, 4 * $count)) as $e) {
            $this->list[] = $e;
        }
        $pos += 4 * $count;
    }

    /*
     * Get portion of string upto null byte (including null byte)
     * 
     * @param string $data Binary data
     * @param int $pos Position of cursor in data
     * 
     * @return string
     */

    private function readCharArray($data, &$pos) {
        // Ищем нулевой байт - символ конца имени
        $nullBytePos = strpos($data, "\x00", $pos);

        // Читаем 
        $s = substr($data, $pos, $nullBytePos - $pos + 1);

        // Изменяем курсор
        $pos = $nullBytePos + 1;

        return $s;
    }

}
