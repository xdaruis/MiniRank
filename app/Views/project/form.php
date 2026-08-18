<h2 class="mb-3">Add a project</h2>

<?php if (!empty($error)): ?>
  <p class="text-danger"><?php echo \App\Core\Response::e($error); ?></p>
<?php endif; ?>

<form method="post" action="index.php?route=project.add" class="mb-3" style="max-width: 420px;">
  <?php echo \App\Core\Csrf::field(); ?>
  <div class="mb-3">
    <label for="domain" class="form-label">Website domain</label>
    <input type="text" id="domain" name="domain" placeholder="example.com" required class="form-control">
  </div>
  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">Add project</button>
    <a href="index.php?route=keyword.list" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>