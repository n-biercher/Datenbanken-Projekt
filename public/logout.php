<!-- Nicolas Biercher Beginn -->
<?php
ob_start();
include_once('include/session_management.php');
session_start();
sitzungBeenden();

header("Location: index.php");
exit();
?>
<!-- Nicolas Biercher Ende -->
