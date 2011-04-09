<?php

include("init.php");

if (isset($_SESSION['user'])) {
    header("Location:topics.php");
    exit;
}

if (empty($_SESSION['signup']) || !is_array($_SESSION['signup'])) {
    header("Location:login.php");
    exit;
}

if (!empty($_POST)) {
    
    $data = $_SESSION['signup'];
    $user = new User($data);
    
    if (isset($_POST['name'])) {
        $user->name = $_POST['name'];
    }
    
    try {
        
        $user->save();
        $_SESSION['user'] = $user->getId();
        unset($_SESSION['signup']);
        header("Location:topics.php");
        
    } catch (Shanty_Mongo_Exception $e) {
        // failed to save user
    }
    
}
?>
<html>
<head>
<title></title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
</head>
<body> 
    <div class="signup"> 
    <?php include("signup_form.php"); ?>
    </div>
</body>
</html>