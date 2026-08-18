<?php
/** @var array $pagination ['page','per_page','total','last','params'] */
if (($pagination['last'] ?? 1) <= 1) {
    return;
}
$page = (int) ($pagination['page'] ?? 1);
$last = (int) ($pagination['last'] ?? 1);
$params = (array) ($pagination['params'] ?? []);

$pg = function (int $n) use ($params): string {
    $base = $params;
    $base['page'] = $n;
    return 'index.php?' . http_build_query($base);
};
?>
<nav aria-label="Pagination">
  <div class="table-responsive">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <small class="text-muted">Page <?php echo \App\Core\Response::e((string) $page); ?> of <?php echo \App\Core\Response::e((string) $last); ?></small>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?php echo \App\Core\Response::e($pg(1)); ?>">&laquo;&laquo; First</a>
        </li>
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?php echo \App\Core\Response::e($pg(max(1, $page - 1))); ?>">&laquo; Prev</a>
        </li>
        <?php
        $from = max(1, $page - 2);
        $to = min($last, $page + 2);
        for ($n = $from; $n <= $to; $n++):
        ?>
          <li class="page-item <?php echo $n === $page ? 'active' : ''; ?>">
            <a class="page-link" href="<?php echo \App\Core\Response::e($pg($n)); ?>"><?php echo \App\Core\Response::e((string) $n); ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?php echo $page >= $last ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?php echo \App\Core\Response::e($pg(min($last, $page + 1))); ?>">Next &raquo;</a>
        </li>
        <li class="page-item <?php echo $page >= $last ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?php echo \App\Core\Response::e($pg($last)); ?>">Last &raquo;&raquo;</a>
        </li>
      </ul>
      <form method="get" action="index.php" class="d-inline-flex align-items-center gap-1">
        <?php foreach ($params as $key => $value): ?>
          <input type="hidden" name="<?php echo \App\Core\Response::e((string) $key); ?>" value="<?php echo \App\Core\Response::e((string) $value); ?>">
        <?php endforeach; ?>
        <label class="visually-hidden" for="page-go">Go to page</label>
        <input type="number" name="page" id="page-go" min="1" max="<?php echo \App\Core\Response::e((string) $last); ?>" value="<?php echo \App\Core\Response::e((string) $page); ?>" class="form-control form-control-sm" style="width:72px;">
        <button type="submit" class="btn btn-sm btn-outline-secondary">Go</button>
      </form>
    </div>
  </div>
</nav>