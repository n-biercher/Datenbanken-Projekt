<?php 

session_start();

function checkLogin($session_name, $weiterleitung) {
    if (!isset($_SESSION[$session_name])) {
        header("Location: " . $weiterleitung);
        exit();
    }
 
}