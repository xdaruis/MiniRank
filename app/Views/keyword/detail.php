<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php?route=keyword.list&project=<?php echo \App\Core\Response::e((string) $projectId); ?>">Keywords</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo \App\Core\Response::e($keyword['phrase']); ?></li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-3">
  <h2 class="mb-0"><?php echo \App\Core\Response::e($keyword['phrase']); ?></h2>
  <?php if (!empty($position)): ?>
    <span class="badge text-bg-success fs-6" title="1 = top result">Position <?php echo \App\Core\Response::e((string) $position); ?></span>
  <?php endif; ?>
  <a class="btn btn-sm btn-outline-secondary ms-auto" href="index.php?route=keyword.export&id=<?php echo \App\Core\Response::e((string) $keyword['id']); ?>&project=<?php echo \App\Core\Response::e((string) $projectId); ?>">Export CSV</a>
</div>

<?php if (empty($history)): ?>
  <p class="text-muted">No position history yet.</p>
<?php else: ?>
  <p class="text-muted"><?php echo \App\Core\Response::e((string) count($history)); ?> days of history.</p>

  <div class="chart-wrap">
    <?php echo \App\Core\Chart::line(array_reverse($history)); ?>
    <div class="chart-tooltip" id="chart-tooltip" role="tooltip"></div>
  </div>

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
            <td class="<?php echo (int) $row['position'] <= 10 ? 'pos-good' : ''; ?>"><?php echo \App\Core\Response::e((string) $row['position']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>