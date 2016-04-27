<?php
include 'Mysql.php';
include 'Curl.php';
include 'Config.php';
$DB = new DB_Mysql($CONFIG);
$CURL=new CUrl();