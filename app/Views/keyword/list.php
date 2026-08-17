<div class="card mb-4">
  <div class="card-body d-flex flex-wrap gap-2 align-items-center">
    <form method="get" action="index.php" class="d-flex gap-2 flex-grow-1" style="max-width:480px;">
      <input type="hidden" name="route" value="keyword.list">
      <input type="search" name="q" value="<?php echo \App\Core\Response::e($search); ?>" placeholder="Search keywords" class="form-control">
      <button type="submit" class="btn btn-outline-secondary">Search</button>
    </form>
    <div class="ms-auto d-flex gap-2">
      <button type="button" id="refresh-positions" class="btn btn-outline-primary">Refresh positions</button>
      <a href="index.php?route=keyword.add" class="btn btn-success">Add keyword</a>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header bg-white">
    <strong><?php echo \App\Core\Response::e((string) count($keywords)); ?></strong> keywords
  </div>
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Keyword</th>
          <th>Position</th>
          <th>Trend</th>
          <th class="text-end"></th>
        </tr>
      </thead>
      <tbody id="keyword-rows">
        <?php foreach ($keywords as $k): $t = $trend($k['id']); ?>
          <tr data-keyword-id="<?php echo \App\Core\Response::e((string) $k['id']); ?>">
            <td><a href="index.php?route=keyword.detail&id=<?php echo \App\Core\Response::e((string) $k['id']); ?>"><?php echo \App\Core\Response::e($k['phrase']); ?></a></td>
            <td class="position"><?php echo \App\Core\Response::e((string) ($k['position'] ?? '-')); ?></td>
            <td class="trend trend-<?php echo \App\Core\Response::e($t); ?>">
              <?php echo \App\Core\Response::e($t === 'improved' ? '▲' : ($t === 'declined' ? '▼' : '=')); ?>
            </td>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <a href="index.php?route=keyword.edit&id=<?php echo \App\Core\Response::e((string) $k['id']); ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="post" action="index.php?route=keyword.delete" class="d-inline" onsubmit="return confirm('Delete this keyword?')">
                  <input type="hidden" name="id" value="<?php echo \App\Core\Response::e((string) $k['id']); ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>