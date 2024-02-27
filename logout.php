<?php

session_start();
$getLang= $_SESSION['lang'];
session_destroy();
session_start();
$_SESSION['lang']=$getLang;
if(isset($_COOKIE['username']) && isset($_COOKIE['password'])){
	unset($_COOKIE['username']);
    unset($_COOKIE['password']);
    setcookie('username', null, -1, '/');
    setcookie('password', null, -1, '/');
}
header('Location:index.php');

 ?>