
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
   <?php  
       include_once "../../header.php";
   ?>

<link rel="stylesheet" type="text/css" href="js/member.css"/>
<script src="js/init.js" type="text/javascript"></script>
<script type='text/javascript'>
    $(document).ready(function(){
        group_get();
        $('#button_clear').click(function(){
            $('.group,.group_for_steel').removeClass('selected');
            $("#db_results").html('Selection has been cleared');
            group_get_properties();
        });

        $('#button_copy_left').click(function(){
            group_copy('LEFT');
            group_get();
            group_get_properties();
        });

         $('#button_copy_right').click(function(){
            group_copy('RIGHT');
            group_get();
            group_get_properties();
        });

        //нажатие кнопки OK
        $("#button_ok").click ( function() {
                group_set_properties();
                group_get_properties();
        });

        //нажатие кнопки сортировка по возрастанию
        $("#button_sort_asc").click ( function() {
                group_sort('ASC');
                group_get();
        });

        //нажатие кнопки сортировка по убыванию
        $("#button_sort_desc").click ( function() {
                group_sort('DESC');
                group_get();
        });

        //нажатие кнопки поиска
        $("#button_find").click ( function() {
                $('#mask, .window').hide();
                group_find($('#reg_exp').val());
        });

        //нажатие кнопки WORD
        $("#button_word").click ( function() {
                group_to_word(0);
        });

        //нажатие кнопки WORD УНИФИКАЦИЯ
        $("#button_word_unif").click ( function() {
                group_to_word(1);
        });
        

        $('.window .close').click(function (e) {
            e.preventDefault();
            $('#mask, .window').hide();
        });

        $('#mask').click(function () {
            $(this).hide();
            $('.window').hide();
        });

    });

    //нажатие + кроме input'ов
    $("*").keyup( function(e) {
        if (e.shiftKey && event.keyCode == 70) {
            e.preventDefault();

            var maskHeight = $(document).height();
            var maskWidth = $(window).width();

            $('#mask').css({'width':maskWidth,'height':maskHeight});

            $('#mask').fadeIn(0);
            $('#mask').fadeTo(0,0.5);

            var winH = $(window).height();
            var winW = $(window).width();

            $('#reg_exp_dialog').css('top',  winH/2-$('#reg_exp_dialog').height()/2);
            $('#reg_exp_dialog').css('left', winW/2-$('#reg_exp_dialog').width()/2);

            $('#reg_exp_dialog').fadeIn(0);
            //фокус на input
            $('#reg_exp').focus();
        }
    });


    //нажатие DELETE
    $("*").keyup( function(e) {
        if ( e.keyCode == 46) {
            group_delete();
            $("#block_group").html('');
            $("#block_group_for_steel").html('');
            group_get();
            $("#properties").html('Please, select group..');
        }
    });

    

    

    


    //получает списки групп из базы данных
    function group_get(){
        //используются синхронные запросы
        $.ajax({
           type: "POST",
           url: "./php/get_group.php",
           async: false,
           success: function(data){
            if (data.length>0) $("#block_group").html(data);
           }
         });

         $.ajax({
           type: "POST",
           url: "./php/get_group_for_steel.php",
           async: false,
           success: function(data){
            if (data.length>0) $("#block_group_for_steel").html(data);
           }
         });
         //устанавливаем свойства для загруженных объектов
         $('.group,.group_for_steel').click(function(){
               if ($(this).is('.selected'))  $(this).removeClass('selected');
                   else  $(this).addClass('selected');
               group_get_properties();
        });
//         set_drag();
    }

    //устанавливает параметры для вновь созданных textBlock
//     function set_drag () {
//        $('.group,.group_for_steel')
//            .attr('draggable', 'true')
//            .bind('dragstart', function(event) {
//                event.originalEvent.dataTransfer.setData('text/plain', $(this).html());
//                return true;
//            });
//     }

     //создает списки выбранных элементов
     function create_group_list () {
         var mas_group = [];
         var mas_group_fs = [];
         
         $('.group.selected').each(function(){
             mas_group.push($(this).attr('value'));
         });

         $('.group_for_steel.selected').each(function(){
             mas_group_fs.push($(this).attr('value'));
         });

         return {group : mas_group, group_fs : mas_group_fs};
     }
     
     //создает списки свойств
     function create_prop_list () {
         var mas_key = [];
         var mas_value = [];
         $('input:text[name!="id"]').each(function(){
             mas_key.push($(this).attr('name'));
             mas_value.push($(this).attr('value'));
         });
         return {key : mas_key, value : mas_value};
     }

     //удаляет выделенные группы из базы
     function group_delete() {
        var mas = create_group_list();
        $.ajax({
           type: "POST",
           url: "./php/group_delete.php",
           async: false,
           data: {mas_group : mas.group, mas_group_for_steel : mas.group_fs},
           success: function(data){
            if (data.length>0) $("#db_results").html(data);
           }
         });
     }

      //сортирует список групп
     function group_sort(direction) {
        $.ajax({
           type: "POST",
           url: "./php/group_sort.php",
           async: false,
           data: {direction:direction},
           success: function(data){
            if (data.length>0) $("#db_results").html(data);
           }
         });
     }

      //получает свойства для выделенных групп
     function group_get_properties() {
        var mas = create_group_list();
        $.ajax({
           type: "POST",
           url: "./php/group_get_properties.php",
           data: {mas_group : mas.group, mas_group_for_steel : mas.group_fs},
           success: function(data){
            if (data.length>0) $("#properties").html(data);
           }
         });
     }

     //присваивает свойства выделенным группам
     function group_set_properties() {
        var selected = create_group_list();
        var prop = create_prop_list();
        $.ajax({
           type: "POST",
           url: "./php/group_set_properties.php",
           async: false,
           data: {mas_group : selected.group, mas_group_for_steel : selected.group_fs,
                    mas_key : prop.key, mas_value : prop.value},
           success: function(data){
            if (data.length>0) $("#db_results").html(data);
           }
         });
     }

     //копирует группы
     function group_copy(d) {
        var selected = create_group_list();
        var list;
        if (d == 'LEFT') list = selected.group_fs;
        if (d == 'RIGHT') list = selected.group;
        $.ajax({
           type: "POST",
           async: false,
           url: "./php/group_copy.php",
           data: {list : list, copy_direction : d},
           success: function(data){
            if (data.length>0) $("#db_results").html(data);
           }
         });
     }

     //поиск группы
     function group_find(reg_exp) {
        $.ajax({
           type: "POST",
           url: "./php/group_find.php",
           data: {reg_exp : reg_exp},
           success: function(data){
            if (data.length>0) {
                $("#db_results").html('Groups were founded.');
//                $("#db_results").html(data);
                var list = JSON.parse(data);
                $('.group,.group_for_steel').removeClass('selected');
                for (var i=0; i<list.group.length; i++)
                    $('.group[value='+list.group[i]+']').addClass('selected');
                for (var i=0; i<list.group_for_steel.length; i++)
                    $('.group_for_steel[value='+list.group_for_steel[i]+']').addClass('selected');

                 group_get_properties();
            }
           }
         });
     }

     //сохраняет РСУ для выделенных групп в Word
     function group_to_word(u) {
        var mas = create_group_list();
        $.ajax({
           type: "POST",
           url: "../rsu/rsu_to_word.php",
           data: {unification: u, mas_group : mas.group, mas_group_for_steel : mas.group_fs},
           success: function(data){
            if (data.length>0) $("#db_results").html(data);
           }
         });
     }
</script>

</head>

<body>

<?php    include_once "../../menu.php"; ?>
<div id="navigation-block">
            <h2>What's wrong? Groups of members..</h2>
</div>
<div id="group">
        <div id="block_group">
        </div>
</div>

<div id="group_for_steel">
        <div id="block_group_for_steel">
        </div>
</div>

<div id="db_results"></div>
<div id="properties"></div>

<div id="commands">
    <img src="./pic/ok.jpg" width="32px" height="32px" id="button_ok"/>
    <img src="./pic/clear.jpg" width="32px" height="32px" id="button_clear" title="Clear selection"/>
    <img src="./pic/left_arrow.jpg" width="32px" height="32px" id="button_copy_left" title="Copy selected group to LEFT"/>
    <img src="./pic/right_arrow.jpg" width="32px" height="32px" id="button_copy_right" title="Copy selected group to RIGHT"/>
    <img src="./pic/sort_asc.jpg" width="32px" height="32px" id="button_sort_asc" title="Сортировка по возрастанию"/>
    <img src="./pic/sort_desc.jpg" width="32px" height="32px" id="button_sort_desc" title="Сортировка по убыванию"/>
    <img src="./pic/word.jpg" width="32px" height="32px" id="button_word" title="Export RSU to WORD"/>
    <img src="./pic/word_unif.jpg" width="32px" height="32px" id="button_word_unif" title="Export unificate RSU to WORD"/>
</div>


<div id="boxes">
    <!-- НАчало формы поиска -->
    <div id="reg_exp_dialog" class="window">
        <div class="d-header">
            <b><font color="white">Например: "L:Балка", "R:+4.000" и т.д.</font></b>
            <input id="reg_exp" type="text" />
            <img src="./pic/search.png" width="32px" height="32px" id="button_find"/>
        </div>
    </div>
</div>
    
<!-- Маска, которая затемняет весь экран -->
<div id="mask"></div>

</body>
</html> 