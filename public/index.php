<?php
require "../init.php";
if (empty($_SESSION['user'])) {
    header("Location:login.php");
    exit;    
} else {
    header("Location:topics.php");
    exit;
}
?>