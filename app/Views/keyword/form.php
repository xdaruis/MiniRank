<h2><?php echo $keyword ? 'Edit keyword' : 'Add keyword'; ?></h2>

<?php if (!empty($error)): ?>
  <p class="error"><?php echo \App\Core\Response::e($error); ?></p>
<?php endif; ?>

<form method="post" action="index.php?route=<?php echo \App\Core\Response::e($action); ?>">
  <label>
    Phrase
    <input type="text" name="phrase" value="<?php echo \App\Core\Response::e($keyword['phrase'] ?? ''); ?>" required>
  </label>
  <button type="submit">Save</button>
</form>

<a href="index.php?route=keyword.list">Back</a>
