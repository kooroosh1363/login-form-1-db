<?php

$sname= "localhost";
$unmae= "root";
$password = "";

$db_name = "login-form-1";

$conn = mysqli_connect($sname, $unmae, $password, $db_name);

if (!$conn) {
	echo "Connection failed!";
}