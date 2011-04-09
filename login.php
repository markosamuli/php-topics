<?php

header('Content-type: text/html; charset=utf-8');
include("init.php");

if (empty($_POST)) {
    
    if (!empty($_SESSION['user'])) {
        header("Location:topics.php");
        exit;
    }
    
} else {
    
    if (isset($_SESSION['user'])) {
        $_SESSION['user'] = null;
    }

    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $user = User::one(array('email' => $_POST['email']));
        if ($user === null) {
            // User does not exist, create new user
            $data = array('email' => $_POST['email'], 'password' => md5($_POST['password']));
            $_SESSION['signup'] = $data;
            header("Location:signup.php");
            exit;
        } else {
            // Try to login
            if ($user->password == md5($_POST['password'])) {
                // Password ok, log user in
                $_SESSION['user'] = $user->getId();
                header("Location:topics.php");
                exit;
            }
        }
    }
}
?>
<html>
<head>
<title></title>
<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
</head>
<body>  
    <div class="login">
    <?php include("login_form.php"); ?>
    </div>
</body>
</html>
        