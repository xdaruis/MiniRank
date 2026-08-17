<div class="row justify-content-center mt-4">
  <div class="col-sm-8 col-md-6 col-lg-5 col-xl-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="card-title text-center mb-1">Register</h2>
        <p class="text-muted text-center mb-4">Create your free account</p>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger py-2"><?php echo \App\Core\Response::e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?route=auth.register">
          <?php echo \App\Core\Csrf::field(); ?>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" required autofocus class="form-control">
          </div>
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" required minlength="8" class="form-control">
            <div class="form-text">At least 8 characters</div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <div class="text-center mt-3">
          <p class="mb-1 text-muted">Already have an account?</p>
          <a href="index.php?route=auth.login" class="btn btn-sm btn-outline-primary">Back to login</a>
        </div>
      </div>
    </div>
  </div>
</div>