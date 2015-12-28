<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <?php  include_once 'header.php'; include_once "./php/db_connect.php" ?>
        <script type='text/javascript'>
            $(document).ready(function(){
                $("#db_results").slideUp();

                $("#db_create_button").click(function(s){
                   db_create();
                });

                $("#db_upload_file_txt_button").click(function(s){
                   upload_file_txt();
                });
                
                $("#db_upload_file_spr11_button").click(function(s){
                   upload_file_spr11();
                });
                
                $("#db_upload_file_spr21_button").click(function(s){
                   upload_file_spr21();
                });

                $("#replace_bad_symbol_button").click(function(s){
                   replace_bad_symbol();
                });

                $("#db_upload_file_std_button").click(function(s){
                   upload_file_std();
                });

                $("#db_download_txt_button").click(function(s){
                   download_txt_file();
                });
                
                $("#db_download_spr11_button").click(function(s){
                   download_spr11_file();
                });
                
                $("#db_download_spr21_button").click(function(s){
                   download_spr21_file();
                });

                $("#rsu_to_mysql_button").click(function(s){
                   rsu_to_mysql();
                });

                $("#unification_rsu_button").click(function(s){
                   unification_rsu();
                });

                $('#form_file').ajaxForm(function(data) {
                    if (data.length>0) $("#db_results").html(data);
                }); 

//                var url = (document.location.href).split('/');
//                url.pop(); url = url.join('/');

                $("input[type=file]").filestyle({
                     image: "/img/upload.jpg",
                     imageheight : 48,
                     imagewidth : 48,
                     width : 200
                 });
            });

            function db_create(){
              $("#db_results").show();
              $("#db_results").html('Creating base is begining...');
              $.post("/php/db_create.php", function(data){
                  if (data.length>0) $("#db_results").html(data);
              });
            }

            function upload_file_txt(){
              $("#db_results").show();
              $("#db_results").html('Uploading file is begining...');
              $("#form_file").attr('action',"/php/upload_file_from_txt.php");
              $("#form_file").submit();
            }
            
            function upload_file_spr11(){
              $("#db_results").show();
              $("#db_results").html('Uploading file is begining...');
              $("#form_file").attr('action',"/php/upload_file_from_spr11.php");
              $("#form_file").submit();
            }
            
            function upload_file_spr21(){
              $("#db_results").show();
              $("#db_results").html('Uploading file is begining...');
              $("#form_file").attr('action',"/php/upload_file_from_spr21.php");
              $("#form_file").submit();
            }

            function replace_bad_symbol(){
              $("#db_results").show();
              $("#db_results").html('Uploading file is begining...');
              $("#form_file").attr('action',"/php/replace_bad_symbol.php");
              $("#form_file").submit();
            }

            function upload_file_std(){
              $("#db_results").show();
              $("#db_results").html('Uploading file is begining...');
              $("#form_file").attr('action',"/php/upload_file_from_std.php");
              $("#form_file").submit();
            }

            function download_txt_file(){
              $("#db_results").show();
              $("#db_results").html('Creating TXT file..<br>');
              document.location.href = "php/save_file_to_txt.php";
              $("#db_results").html('TXT file was created.<br>');
            }
            
            function download_spr11_file(){
              $("#db_results").show();
              $("#db_results").html('Creating SPR file..<br>');
              document.location.href = "php/save_file_to_spr11.php";
              $("#db_results").html('SPR file was created.<br>');
            }
            
            function download_spr21_file(){
              $("#db_results").show();
              $("#db_results").html('Creating SPR file..<br>');
              document.location.href = "php/save_file_to_spr21.php";
              $("#db_results").html('SPR file was created.<br>');
            }

            function rsu_to_mysql(){
              $("#db_results").show();
              $("#db_results").html('RSU to MySQL ...');
              $("#form_file_RSU").attr('action',"/php/rsu/rsu.php");
              $("#form_file_RSU").submit();
              
//              $("#db_results").show();
//              $("#db_results").html('RSU -> MySQL ...<br/>');
//              $.ajax({
//               type: "POST",
//               url: "/php/rsu/rsu.php",
//               success: function(data){
//                if (data.length>0) $("#db_results").html(data);
//               }
//             });
            }

            function unification_rsu(){
              $("#db_results").show();
              $("#db_results").html('Идет унификация РСУ ...<br/>');
              $.ajax({
               type: "POST",
               url: "/php/rsu/unification_rsu.php",
               success: function(data){
                if (data.length>0) $("#db_results").html(data);
               }
             });
            }
            
        </script>
    </head>

    <body>

        <div id="navigation-block">
            <h2>What's wrong? Everything..</h2>
            <p>By Noskov Aleksei</p>
            <ul id="sliding-navigation">
                <li class="sliding-element"><h3>Работа с группами элементов</h3></li>
                <li class="sliding-element"><a href="/php/group/member.php">Редактирование групп элементов, групп для подбора и т.д.</a></li>
                <li class="sliding-element"><h3>РСУ</h3></li>
                <li class="sliding-element" id="rsu_to_mysql_button"><a href="#">RSU to MySQL</a></li>
                <li class="sliding-element" id="unification_rsu_button"><a href="#">Унификация РСУ по группам эл-ов для подбора стали</a></li>
                <li class="sliding-element"><h3>Database</h3></li>
                <li class="sliding-element" id="db_create_button"><a href="#">Create</a></li>
                <li class="sliding-element" id="replace_bad_symbol_button"><a href="#">Replace bad symbols in file SCAD *.txt</a></li>
                <li class="sliding-element" id="db_upload_file_txt_button"><a href="#">Upload File SCAD *.txt (без повторителей)</a></li>
                <li class="sliding-element" id="db_upload_file_spr11_button"><a href="#">Upload File SCAD 11 *.spr</a></li>
                <li class="sliding-element" id="db_upload_file_spr21_button"><a href="#">Upload File SCAD 21 *.spr</a></li>
                <li class="sliding-element" id="db_upload_file_std_button"><a href="#">Upload File STAAD *.std</a></li>
                <li class="sliding-element" id="db_download_txt_button"><a href="#">Download TXT File</a></li>
                <li class="sliding-element" id="db_download_spr11_button"><a href="#">Download SCAD 11 *.spr</a></li>
                <li class="sliding-element" id="db_download_spr21_button"><a href="#">Download SCAD 21 *.spr</a></li>

            </ul>

            <p>Choose file:</p>
            <form method="post" id="form_file" action="#" enctype="multipart/form-data">
                <input name="file" type='file'/><br/>
            </form>
            <form method="post" id="form_file_RSU" action="#" enctype="multipart/form-data">
                <p>Загрузите файл РСУ *.F21</p><input name="F21_file" type='file'/><br/>
                <p>Загрузите файл РСУ *.F22</p><input name="F22_file" type='file'/><br/>
            </form>
            
        </div>
        <div id="db_results"></div>
        <?php    include_once 'menu.php'; ?>
    </body>
</html> 