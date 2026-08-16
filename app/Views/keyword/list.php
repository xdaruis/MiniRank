<form method="get" action="index.php">
  <input type="hidden" name="route" value="keyword.list">
  <input type="search" name="q" value="<?php echo \App\Core\Response::e($search); ?>" placeholder="Search keywords">
  <button type="submit">Search</button>
</form>

<button type="button" id="refresh-positions">Refresh positions</button>

<table>
  <thead>
    <tr>
      <th>Keyword</th>
      <th>Position</th>
      <th>Trend</th>
      <th></th>
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
        <td>
          <a href="index.php?route=keyword.edit&id=<?php echo \App\Core\Response::e((string) $k['id']); ?>">Edit</a>
          <form method="post" action="index.php?route=keyword.delete" onsubmit="return confirm('Delete this keyword?')">
            <input type="hidden" name="id" value="<?php echo \App\Core\Response::e((string) $k['id']); ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<a href="index.php?route=keyword.add">Add keyword</a>
