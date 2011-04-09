<?php if (empty($_SESSION['user'])) { ?>
    <div class="login">
    <?php include("login_form.php"); ?>
    </div>
<?php } else { ?>
<div class="navigation">    
    <div class="user">
        <?php 
        $user = User::find($_SESSION['user']);
        ?>
        <span class="name"><?php echo $user->name; ?></span>
        (<span class="email"><?php echo $user->email; ?></span>)
    </div>
    <div class="logout"><a href="logout.php">Logout</a></div>
</div>    
<?php } ?>