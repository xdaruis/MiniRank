<h2 class="mb-3"><?php echo $keyword ? 'Edit keyword' : 'Add keyword'; ?></h2>

<?php if (!empty($error)): ?>
  <p class="text-danger"><?php echo \App\Core\Response::e($error); ?></p>
<?php endif; ?>

<form method="post" action="index.php?route=<?php echo \App\Core\Response::e($action); ?>" class="mb-3">
  <div class="mb-3">
    <label for="phrase" class="form-label">Phrase</label>
    <input type="text" id="phrase" name="phrase" value="<?php echo \App\Core\Response::e($keyword['phrase'] ?? ''); ?>" required class="form-control" style="max-width: 480px;">
  </div>
  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="index.php?route=keyword.list" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>