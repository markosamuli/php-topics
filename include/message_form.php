<?php if (isset($topic) && $topic instanceof Topic) { ?>
<form action="message_create.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?php echo $topic->getId(); ?>" />
  <div>
    <label for="message">What would you like to say?</label>
    <textarea name="message" id="message"></textarea>
  </div>
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 1024 * 1024 * 10; ?>" />
  <input type="file" name="image" />
  <div>
    <button type="submit">Shout it out loud!</button>
  </div>
</form>
<?php } ?>