<?php

namespace php\classes\Document\GroupSteelCheckDocument;

use php\classes\BinaryDataContainer\BinaryDataContainer;

class GroupSteelCheckDocument {

    const GROUP_COUNT_ADDRESS = 1;
    const GROUP_DESCRIPTION_ADDRESS = 9;
    const STEEL_NAME_BYTES_COUNT = 80;
    const DESCRIPTION_BLOCK_BYTES_COUNT = 329;
    
    const DOCUMENT_START_INDEX = 3;

    private $binaryDataContainer;
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
        ['aFC', 'd', 1], // Альфа компонент для гибкости сжатых элементов
        ['aFT', 'd', 1], // Альфа компонент для гибкости растянутых элементов
        ['length_XZ', 'd', 1], // Расчетная длина в пл-ти XoZ
        ['length_XY', 'd', 1], // Расчетная длина в пл-ти XoY
        ['noname1', 'd', 1], // Неизвестно (всегда 1.0)
        ['noname2', 'd', 1], // Неизвестно (всегда 1.0)
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

    public function binaryDataToArray($data) {

        $this->binaryDataContainer = new BinaryDataContainer($data);
        $groupCount = $this->getGroupCount();
        $this->binaryDataContainer->setCursor(self::GROUP_DESCRIPTION_ADDRESS);

        $groups = array();
        for ($i = 0; $i < $groupCount; $i++) {
            $groups[] = $this->readSingleDescriptionBlock();
        }

        for ($i = 0; $i < $groupCount; $i++) {
            $groups[$i] = array_merge($groups[$i], $this->readSingleListBlock());
        }

        return $groups;
    }

    private function getGroupCount() {
        $this->binaryDataContainer->setCursor(self::GROUP_COUNT_ADDRESS);
        return $this->binaryDataContainer->unpackIntValue();
    }

    private function readSingleDescriptionBlock() {

        $steel = $this->decodeText($this->binaryDataContainer->readCharArrayUntilZeroByte(self::STEEL_NAME_BYTES_COUNT));

        $block = $this->binaryDataContainer->unpackPortionByMap(
                self::DESCRIPTION_BLOCK_BYTES_COUNT - self::STEEL_NAME_BYTES_COUNT, $this->descriptionBlockMap);

        $block['steel'] = $steel;

        return $block;
    }

    private function readSingleListBlock() {

        $name = $this->decodeText($this->binaryDataContainer->readCharArrayUntilZeroByte());
        $this->binaryDataContainer->shiftCursor(1); // Zero byte in the end of char array

        $elementCount = (int) $this->binaryDataContainer->unpackIntValue();

        $list = array();
        for ($i = 0; $i < $elementCount; $i++) {
            $list[] = $this->binaryDataContainer->unpackIntValue();
        }

        return array('name' => $name, 'list' => $list);
    }

    private function decodeText($text) {
        return iconv('Windows-1251', 'UTF-8', $text);
    }

    public function arrayToBinaryData($array) {
        
        $this->binaryDataContainer = new BinaryDataContainer('');
        
        $binaryData = '';
        $binaryData .= $this->binaryDataContainer->packCharValue(self::DOCUMENT_START_INDEX);
        $binaryData .= $this->binaryDataContainer->packIntValue(count($array));
        $binaryData .= $this->binaryDataContainer->packIntValue(0);
        
        foreach ($array as $row) {
            $binaryData .= $this->writeSingleDescriptionBlock($row);
        }
        
        foreach ($array as $row) {
            $binaryData .= $this->writeSingleListBlock($row);
        }
        
        return $binaryData;
    }
    
    private function writeSingleDescriptionBlock($array) {

        $binaryData = '';
        
        $steel = $this->encodeText($array['steel']);
        $bytesCount = self::STEEL_NAME_BYTES_COUNT - strlen($steel);
        $binaryData .= $steel . pack('x'.$bytesCount);
        
        $binaryData .= $this->binaryDataContainer->packPortionByMap($array, $this->descriptionBlockMap);
        
        return $binaryData;
    }
    
    private function writeSingleListBlock($array) {
        
        $binaryData = '';

        $binaryData .= $this->encodeText($array['name'])."\x00";
        
        $list = $array['list'];
        $binaryData .= $this->binaryDataContainer->packIntValue(count($list));
        
        foreach ($list as $id) {
            $binaryData .= $this->binaryDataContainer->packIntValue($id);
        }
        
        return $binaryData;
    }

    private function encodeText($text) {
        return iconv('UTF-8', 'Windows-1251', $text);
    }

}
