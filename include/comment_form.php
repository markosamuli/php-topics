<?php if (isset($post) && $post instanceof Post) { ?>
<form action="comment_create.php" method="post">
  <input type="hidden" name="id" value="<?php echo $post->getId(); ?>" />
  <?php if (isset($topic)) { ?>
  <input type="hidden" name="return" value="topic" />
  <?php } ?>
  <div>
    <label for="comment-post-<?php echo $post->getId(); ?>">My two cents...</label>
    <textarea id="comment-post-<?php echo $post->getId(); ?>" name="comment"></textarea>
  </div>
  <div>
    <button type="submit">Blurb!</button>
  </div>
</form>
<?php } ?>