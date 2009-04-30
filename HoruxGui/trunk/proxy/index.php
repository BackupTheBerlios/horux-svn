<?php

	session_start();

    $page = $lang = "";

    if(isset($_GET['page']))
        $page = $_GET['page'];

    if(isset($_GET['lang']))
        $lang = $_GET['lang'];

	echo '<iframe src="http://help.horux.ch/index.php?key='.$_SESSION['helpKey'].'&page='.$page.'&lang='.$lang.'" scrolling="auto" width="100%" height="100%" frameborder="0" style="background-color:#fff"/>';
?>