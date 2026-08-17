<form method="get" action="index.php" class="card mb-4">
  <div class="card-body d-flex flex-wrap gap-2 align-items-center">
    <input type="hidden" name="route" value="keyword.list">
    <input type="search" name="q" value="<?php echo \App\Core\Response::e($search); ?>" placeholder="Search keywords" class="form-control" style="max-width:220px;">
    <select name="move" class="form-select" style="max-width:160px;">
      <option value="">All trends</option>
      <option value="improved"<?php echo $move === 'improved' ? ' selected' : ''; ?>>Improved ▲</option>
      <option value="declined"<?php echo $move === 'declined' ? ' selected' : ''; ?>>Declined ▼</option>
      <option value="stable"<?php echo $move === 'stable' ? ' selected' : ''; ?>>Stable =</option>
    </select>
    <label class="d-flex align-items-center gap-1">
      From
      <input type="number" name="pos_min" min="1" max="100" value="<?php echo $pos_min > 0 ? (int) $pos_min : ''; ?>" class="form-control" style="max-width:80px;">
    </label>
    <label class="d-flex align-items-center gap-1">
      To
      <input type="number" name="pos_max" min="1" max="100" value="<?php echo $pos_max > 0 ? (int) $pos_max : ''; ?>" class="form-control" style="max-width:80px;">
    </label>
    <button type="submit" class="btn btn-outline-primary">Apply</button>
    <a href="index.php?route=keyword.list" class="btn btn-outline-secondary">Reset</a>
    <span class="form-text m-0">1 = top result</span>
  </div>
</form>

<div class="d-flex justify-content-end gap-2 mb-3">
  <button type="button" id="refresh-positions" class="btn btn-primary">Refresh positions</button>
  <a href="index.php?route=keyword.add" class="btn btn-success">Add keyword</a>
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
            <td class="position<?php echo (int) ($k['position'] ?? 0) > 0 && (int) ($k['position'] ?? 0) <= 10 ? ' pos-good' : ''; ?>"><?php echo \App\Core\Response::e((string) ($k['position'] ?? '-')); ?></td>
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