<form action="login.php" method="post">
    <div class="input">
        <label for="email">Email</label>
        <input type="text" name="email" id="email"
               value="<?php echo (isset($_POST['email']) ? $_POST['email'] : null); ?>" />
    </div>
    <div class="input">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" />
    </div>
    <button type="submit">Login</button>
</form>