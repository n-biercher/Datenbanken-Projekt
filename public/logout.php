<?php
// Nicolas Biercher Beginn
include_once('include/session_management.php');
session_start();
sitzungBeenden();

header("Location: index.php");
exit();
// Nicolas Biercher Ende
