<h2><?php echo $keyword ? 'Edit keyword' : 'Add keyword'; ?></h2>

<form method="post" action="index.php?route=<?php echo \App\Core\Response::e($action); ?>">
  <label>
    Phrase
    <input type="text" name="phrase" value="<?php echo \App\Core\Response::e($keyword['phrase'] ?? ''); ?>" required>
  </label>
  <button type="submit">Save</button>
</form>

<a href="index.php?route=keyword.list">Back</a>
