<?php
//запись свойств
function set_property($base_name, $id_list, $key_list, $value_list, $description='') {
    if ($id_list && $key_list && $value_list) {
        $sql = 'UPDATE '.$base_name.' SET ';
        $set_mas = array();
        for ($i=0;$i<count($key_list);$i++) {

            $result = mysql_query("SELECT * FROM information_schema.columns WHERE
                table_schema =  '".scad."' AND table_name =  '".$base_name."' AND column_name =  '".$key_list[$i]."'");
            if ($value_list[$i] != '*DIFFERENT*' && mysql_num_rows($result) > 0)
                $set_mas[] = $key_list[$i]." = '".$value_list[$i]."'";
        }   
        $sql .= implode(',', $set_mas);
        $sql .= ' WHERE id IN ('.implode(',',$id_list).')';
//        echo $sql.'<br/>';
        //если массив set_mas не пустой
        if (count($set_mas) > 0) mysql_query($sql);
        switch (mysql_errno()) {
         case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
         default:
            if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
            else echo 'Groups '.$description.' '.implode(', ',$id_list).' were сhanged.<br/>';
        }
    }
}

include_once '../../db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//запись свойств
echo 'PROP<br/>';
set_property(member_group, $_POST['mas_group'], $_POST['mas_key'], $_POST['mas_value'], 'of members');
set_property(member_group_for_steel, $_POST['mas_group_for_steel'],$_POST['mas_key'], $_POST['mas_value'], 'of members for steel');

//закрываем соединение с базой
 mysql_close($dbh);
?>