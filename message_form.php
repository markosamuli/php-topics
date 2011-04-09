<?php if (isset($topic) && $topic instanceof Topic) { ?>
<form action="message_create.php" method="post">
  <input type="hidden" name="id" value="<?php echo $topic->getId(); ?>" />
  <div>
    <label for="message">What would you like to say?</label>
    <textarea name="message" id="message"></textarea>
  </div>
  <div>
    <button type="submit">Shout it out loud!</button>
  </div>
</form>
<?php } ?>