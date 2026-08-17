<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MiniRank</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
  <header class="bg-dark text-white py-3 mb-4">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h1 class="fs-3 mb-0"><a class="text-white text-decoration-none" href="index.php?route=keyword.list">MiniRank</a></h1>
      <span class="text-white-50 fs-6">Keyword position tracker</span>
    </div>
  </header>
  <main class="container pb-5 flex-grow-1">
    <?php echo $content(); ?>
  </main>
  <footer class="text-muted text-center py-4 border-top mt-auto">
    <small>MiniRank &middot; simulated demo data</small>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/app.js"></script>
</body>
</html>