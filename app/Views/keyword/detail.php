<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php?route=keyword.list">Keywords</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo \App\Core\Response::e($keyword['phrase']); ?></li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-3">
  <h2 class="mb-0"><?php echo \App\Core\Response::e($keyword['phrase']); ?></h2>
  <?php if (!empty($keyword['position'])): ?>
    <span class="badge text-bg-success fs-6">Position <?php echo \App\Core\Response::e((string) $keyword['position']); ?></span>
  <?php endif; ?>
</div>

<?php if (empty($history)): ?>
  <p class="text-muted">No position history yet.</p>
<?php else: ?>
  <p class="text-muted"><?php echo \App\Core\Response::e((string) count($history)); ?> days of history.</p>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Date</th>
          <th>Position</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $row): ?>
          <tr>
            <td><?php echo \App\Core\Response::e((string) $row['captured_at']); ?></td>
            <td><?php echo \App\Core\Response::e((string) $row['position']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>