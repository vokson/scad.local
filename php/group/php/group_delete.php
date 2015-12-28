<?php
//удаляет из базы список строк
function delete($base_name,$id_list,$description='') {
    if ($id_list) {
        $sql = "DELETE FROM ".$base_name." WHERE id IN (".implode(',',$id_list).')';
//        echo $sql.'<br/>';
            mysql_query($sql);
            switch (mysql_errno()) {
             case 1146: echo "<b>Table ".$base_name." doesn't exist. Please create DB.</b><br>";break;
             default:
                if (mysql_errno () > 0) echo mysql_errno().'  '.mysql_error().'<br>';
                    else echo 'Groups '.$description.' '.implode(', ',$id_list).' were deleted.<br/>';
            }
    }
}

include_once '../../db_connect.php';

//подключаемся к базе
$dbh = mysql_connect(host, user, pswd) or die("Не могу соединиться с MySQL.");
mysql_select_db(SCAD) or die("Не могу подключиться к базе.");
$res = mysql_query("SET NAMES utf8");

//print_r($_POST);
//удаление списки отмеченных элементов
delete(member_group, $_POST['mas_group'], 'of members');
delete(member_group_for_steel, $_POST['mas_group_for_steel'], 'of members for steel');

//закрываем соединение с базой
 mysql_close($dbh);
?>