<?php
//формируем список имен свойств
function get_prop_list ($base_name,&$mas) {
    $result = mysql_query('EXPLAIN '.$base_name);
    if (mysql_num_rows($result) > 0)
       while($row = mysql_fetch_array($result, MYSQL_ASSOC))
            $mas[$row['Field']] = NULL;
}

//ищет свойства
function get_property($base_name,$id_list,&$prop_list) {
    if ($id_list) {
        $sql = "SELECT * FROM ".$base_name." WHERE id IN (".implode(',',$id_list).')';
//        echo $sql.'<br/>';
            mysql_query($sql);
            switch (mysql_errno()) {
             case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
             default:
                if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0)
                   while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                       //для каждой строки перебираем все свойства
                       foreach ($prop_list as $key => $value) {
//                           echo "KEY=>VALUE : $key=>$value</br>";
                           if (isset($row[$key]) && $value != '*DIFFERENT*') {
                               if ($value == NULL) $prop_list[$key] = $row[$key];
                               else
                                   if ($value != $row[$key]) $prop_list[$key]='*DIFFERENT*';
                           }
                       }    
            }
    }
}

include_once '../../db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//формируем список имен свойств
get_prop_list(member_group, $prop_list);
get_prop_list(member_group_for_steel, $prop_list);

//чтение свойств
get_property(member_group, $_POST['mas_group'],$prop_list);
get_property(member_group_for_steel, $_POST['mas_group_for_steel'],$prop_list);

//удаляем ненужные элементы
foreach ($prop_list as $key => $value)
    if ($value == NULL) unset($prop_list[$key]);

//загружаем переводы в массив
$translator = array();
$sql = "SELECT * FROM ".translate_group;
mysql_query($sql);
switch (mysql_errno()) {
 case 1146: echo "<b>Table ".translate_group." doesn't exist. Please create DB.</b><br>";break;
 default:
    if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0)
       while($row = mysql_fetch_array($result, MYSQL_ASSOC))
            $translator[$row['english']] = array('edit' => $row['edit'],
                'russian' => $row['russian'],'description' => $row['description']);
}

echo 'PROPERTIES:<br/>';
echo '<table><tbody>';
foreach ($prop_list as $key => $value) {
    $s = '<tr><td width="150px">'.$translator[$key]['russian'].
         '</td><td width="150px"><input id="property" type="text" value="'.$value.'"';
    $s .= ' name="'.$key.'"';
    $s .= ' title="'.$translator[$key]['description'].'"';
    if ($translator[$key]['edit'] == 0) $s .= ' disabled';
    $s .= '/></td></tr>';
    echo $s;
}
echo '</tbody></table>';

//закрываем соединение с базой
 mysql_close($dbh);
?>