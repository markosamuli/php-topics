<form action="signup.php" method="post">
    <div class="input">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" 
               value="<?php echo (isset($_POST['name']) ? $_POST['name'] : null); ?>" />
    </div>
    <button type="submit">Signup</button>
</form>