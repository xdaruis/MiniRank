<h2><?php echo \App\Core\Response::e($keyword['phrase']); ?></h2>

<?php if (empty($history)): ?>
  <p>No position history yet.</p>
<?php else: ?>
  <p><?php echo \App\Core\Response::e((string) count($history)); ?> days of history.</p>

  <table>
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
<?php endif; ?>

<a href="index.php?route=keyword.list">Back to list</a>