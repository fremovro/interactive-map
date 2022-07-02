<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
		<meta charset=utf-8 > 
		<link rel="stylesheet" href=".\admin_map_styles.css">
		<link rel="stylesheet" href=".\reset.css">
		<title>
			Интерактивная карта
		</title>
	</head>
	<body>
        <div class="map" id="map">
            <div class="map_field" id="map_field"></div>
            <img src="https://cdn-icons-png.flaticon.com/512/64/64022.png" id="trash">
        </div>
        <div class="header">
				<div class="logo_text">
					<p class="site"><img src = "pics/site.png" width="30" height="30"><br> parma.ru </p>
					<p class="mail"><img src = "pics/mail.png" width="30" height="30"><br> hello@parma.ru </p>
					<p class="phone"><img src = "pics/phone.png" width="30" height="30"><br>+7 (342) 254-34-34</p>
					<p class="site1"> АДМИНИСТРИРОВАНИЕ </p>
				</div>
            </div>
        <div class="header_w_but" id="left">
            <input type="button" class="map_button" id="but" name="add_button" value="Добавить стол">
            <form action="timetable.php" method="post">
                <input type="submit" name="timetable" value="История бронирований">
            </form>
			<input type="button" class="map_button" name="exit" value="Выйти" onclick="outLogin();">
        </div>
	</body>
    //
    <script>
        //
        var query_tables = 
        "<?php
            $link1 = mysqli_connect("localhost", "user","1234","map");
            $result1 = mysqli_query($link1,"SELECT * FROM work_table");
            foreach($result1 as $row)
                echo (string) $row['id_table'] . ' ' . (string) $row['pos_x'] . ' ' . (string) $row['pos_y'] . ' ' . 
                    (string) $row['rotate'] . ' ' . (string) $row['status'] . 'END';
        ?>";
        var query_booking = 
        "<?php
            $link1 = mysqli_connect("localhost", "user","1234","map");
            $result1 = mysqli_query($link1,"SELECT * FROM booking");
            foreach($result1 as $row)
                echo (string) $row['id_booking'] . ' ' . (string) $row['date_start'] . ' ' . (string) $row['date_end'] . ' ' . 
                    (string) $row['id_table'] . ' ' . (string) $row['id_user'] . 'END';
        ?>";
        // Отрисовка существующих столов
        if(query_tables.length > 0) // Проверка наличия столов в бд
        {
            let temp = query_tables.split('END');
            let tables = [];
            for(var i = 0; i < temp.length - 1; i++)
                tables[i] = temp[i].split(' ');
            //
            var windowInnerWidth = document.documentElement.clientWidth;
            var windowInnerHeight = document.documentElement.clientHeight;
            var table_width = windowInnerWidth/100*15; // 10 - ширина стола (%) !
            var table_height = windowInnerWidth/100*5; // 3 - высота стола (%) !
            //
            for(var i = 0; i < tables.length; i++) // (-1)?
            {
                var btn = document.createElement ('input');
                //
                var color; 
                if(tables[i][4] == '1') color = '#ff0000'; else color = '#4dbeaa';
                var pos_x = tables[i][1] * (windowInnerWidth/100); // Определение координат стола
                var pos_y = tables[i][2] * (windowInnerHeight/100);
                btn.id = "id" + tables[i][0]; 
                btn.type = 'button'; 
                btn.value="№" + tables[i][0] + "\nСвободно"; // занято свободно
                btn.style.cssText = 'background: ' + color + '; left: ' + pos_x +'px; top: ' + pos_y + 'px;';
                btn.style.transform = 'rotate(' + tables[i][3] + 'deg)';
                document.getElementById('map').appendChild (btn);
                //
                all_move(btn, windowInnerWidth, windowInnerHeight);
            }
        }
        
        if(query_booking.length > 0)
        {
            let temp = query_booking.split('END');
            let booking = [];
            for(var i = 0; i < temp.length - 1; i++)
                booking[i] = temp[i].split(' ');
            //
            let temp1 = query_tables.split('END');
            let tables1 = [];
            for(var i = 0; i < temp1.length - 1; i++)
                tables1[i] = temp1[i].split(' ');
            //
            //
            for(var i = 0; i < tables1.length; i++)
            {
                for(var j = 0; j < booking.length; j++)
                {
                    if(tables1[i][0]==booking[j][3])
                    {
                        var this_table = document.getElementById("id" + tables1[i][0]);
                        var temp_time = new Date(booking[j][2]);
                        temp_time.setDate(temp_time.getDate());
                        this_table.value = "№"+tables1[i][0]+"\n Занято до: " + temp_time.getDate() + '.' + temp_time.getMonth() + '.' + temp_time.getFullYear();
                    }
                }
            }
            //
            for(var i = 0; i < booking.length; i++) // (-1)?
                for(var j = 0; j < tables1.length; j++)
                    if(tables1[j][0] == booking[i][3])
                        if(tables1[j][4] == '1')
                            if(new Date() > new Date(booking[i][2]))
                            {
                                console.log(booking[i][2]);
                                var btn=document.getElementById('id'+booking[i][3]);
                                btn.style.background='#4dbeaa';
                                btn.value="№"+tables1[j][0]+"\nСвободно";
                                free_table(btn, booking[i][0], booking[i][3]);
                            }
        }
        //
        $(document).on('click', '#but', function()
        { 
            var windowInnerWidth = document.documentElement.clientWidth;
            var windowInnerHeight = document.documentElement.clientHeight;
            //
            var pos_x = 2.7;
            var pos_y = 30;
            var pos_x_px = (windowInnerWidth/100)*pos_x;;
            var pos_y_px = (windowInnerHeight/100)*pos_y;
            //
            $.ajax(
            {
                url: 'add_table.php',
                type: 'POST',
                data: {
                    pos_x: pos_x,
                    pos_y: pos_y,
                },
                success: function(data) 
                {
                    var btn = document.createElement ('input');
                    btn.style.zIndex = 1;
                    var color = '#4dbeaa';
                    // console.log(windowInnerWidth + ' ' + windowInnerHeight + ' ' + pos_x + ' ' + pos_y);
                    btn.id = "id" + data; btn.type = 'button'; btn.value = "№" + data+"\nСвободно";
                    btn.style.cssText = 'box-shadow: 0 0 10px rgba(0,0,0,0.2); position: absolute; box-sizing: border-box; height:13%; font-size: 16px;text-shadow: 1px 1px 2px black; color: white; font-family: "Montserrat", sans-serif; border: 0; border-radius: 11px; width:15%; background: ' + color + '; left: ' + pos_x_px +'px; top: ' + pos_y_px + 'px;';
                    btn.style.transform = 'rotate(0deg)';
                    document.getElementById('map').appendChild (btn);
                    //
                    all_move(btn, windowInnerWidth, windowInnerHeight); 
                }, 
                error: function() {console.log('ERROR');}
            });
        });
        //
        function all_move(btn, windowInnerWidth, windowInnerHeight)
        {
            var trash = document.getElementById("trash");
            var trash_pos_right = windowInnerWidth/100*10;
            var trash_pos_top = windowInnerHeight/100*75;
            //
            btn.onmouseover = function(e)
            {
                var table = e.currentTarget;
                sessionStorage.setItem('id', table.id);
                //
                function mykey(event)
                {
                    var table = document.getElementById(sessionStorage.getItem('id'));
                    var id_table = table.id.substr(2);
                    var rotate;
                    switch(event.key)
                    {
                        case 'ArrowLeft':
                            rotate = '270';
                            table.style.transform = "rotate(270deg)";
                            break;
                        case 'ArrowRight':
                            rotate = '90';
                            table.style.transform = "rotate(90deg)";
                            break;
                        case 'ArrowUp':
                            rotate = '0';
                            table.style.transform = "rotate(0deg)";
                            break;
                        case 'ArrowDown':
                            rotate = '180';
                            table.style.transform = "rotate(180deg)";
                            break;
                    }
                    $.ajax({
                        url: 'update_rotate.php',
                        type: 'POST',
                        data: 
                        {
                            rotate: rotate,
                            id_table: id_table,
                        },
                        success: function(data) {}, 
                        error: function() {console.log('ERROR');}
                    });
                }
                //
                document.addEventListener('keydown', mykey);
                //
                table.onmouseout = function(e)
                {
                    document.removeEventListener('keydown', mykey);
                    sessionStorage.setItem('id', "");
                }
            }
            //
            btn.onmousedown = function(e) 
            {
                var table = e.currentTarget;
                    
                table.style.position = 'absolute';
                //
                moveAt(e);
                //
                document.body.appendChild(table);
                table.style.zIndex = 1;
                //
                function moveAt(e) 
                {
                    var left_border = windowInnerWidth/100*4;
                    var right_border = windowInnerWidth/100*96;
                    var top_border = windowInnerHeight/100*27.7;
                    var bottom_border = windowInnerHeight/100*92.4;
                    var deg = table.style.transform;
                    //
                    if(deg == "rotate(0deg)" || deg == "rotate(180deg)")
                    {
                        if((e.pageX - table.offsetWidth / 2) > left_border && (e.pageX + table.offsetWidth / 2) < right_border)
                            if((e.pageY - table.offsetHeight / 2) > top_border && (e.pageY + table.offsetHeight / 2) < bottom_border)
                            {
                                table.style.left = e.pageX - table.offsetWidth / 2 + 'px';
                                table.style.top = e.pageY - table.offsetHeight / 2 + 'px';
                            }
                    }
                    else
                    {
                        if((e.pageX - table.offsetHeight / 2) > left_border && (e.pageX + table.offsetHeight / 2) < right_border)
                        {
                            if((e.pageY - table.offsetWidth / 2) > top_border && (e.pageY + table.offsetWidth / 2) < bottom_border)
                            {
                                table.style.left = e.pageX - table.offsetWidth / 2 + 'px';
                                table.style.top = e.pageY - table.offsetHeight / 2 + 'px';
                            }
                        }
                    }
                    if((e.pageX - table.offsetWidth / 2) < trash_pos_right && (e.pageY - table.offsetHeight / 2) > trash_pos_top && btn.style.background!='#ff0000')
                        trash.style.background = 'pink';
                    else trash.style.background = '';
                }
                //
                document.onmousemove = function(e) 
                {                                
                    moveAt(e);
                }
                //
                table.onmouseup = function(e) 
                {
                    document.onmousemove = null;
                    table.onmouseup = null; 
                    if((e.pageX - table.offsetWidth / 2) < trash_pos_right && (e.pageY - table.offsetHeight / 2) > trash_pos_top && btn.style.background!='#ff0000')
                    {
                        var child = document.getElementById(table.id); document.body.removeChild(child);
                        let temp = table.id;
                        let id_table = temp.substr(2);
                        var trash = document.getElementById("trash");
                        trash.style.background = '';
                        $.ajax({
                            url: 'delete_btn.php',
                            type: 'POST',
                            data: 
                            {
                                id_table: id_table,
                            },
                            success: function(data) {}, 
                            error: function() {console.log('ERROR');}
                        })
                    }
                    else
                    {
                        var pos_x = (e.pageX-table.offsetWidth/2)/(windowInnerWidth/100);
                        var pos_y = (e.pageY-table.offsetHeight/2)/(windowInnerHeight/100);
                        console.log(windowInnerWidth + ' ' + windowInnerHeight + ' ' + pos_x*(windowInnerWidth/100) + ' ' + pos_y*(windowInnerHeight/100));
                        let temp = table.id;
                        console.log(table.id);
                        let id_table = temp.substr(2);
                        $.ajax({
                            url: 'update_position.php',
                            type: 'POST',
                            data: 
                            {
                                pos_x: pos_x,
                                pos_y: pos_y,
                                id_table: id_table,
                            },
                            success: function(data) {}, 
                            error: function() {console.log('ERROR');}
                        })
                    }
                }                    
            }
        }
        //
        <?php
            if (empty($_SESSION['login']))
               header("Location: authorization.php");
        ?>
        //
        window.onresize = function(e)
        {
            location.reload();
        }
        //
        function outLogin()
        {
            window.location.href = "exit.php";
        }
    </script>
</html>