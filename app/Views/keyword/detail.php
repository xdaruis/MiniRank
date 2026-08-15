<h2><?php echo \App\Core\Response::e($keyword['phrase']); ?></h2>

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

<a href="index.php?route=keyword.list">Back</a>
