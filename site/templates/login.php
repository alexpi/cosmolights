<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cosmolights | login</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">
  <?= css(['assets/reset.css', 'assets/styles.css', 'assets/login.css']) ?>
</head>
<body>
  
  <main class="container">
    <h1 class="event-title">Cosmolights</h1>   
      
    <form method="post" action="<?= $page->url() ?>">
      <h1><?= $page->title() ?></h1>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= esc(get('email')) ?>">
      </div>
      <div>
        <label for="password">Κωδικός</label>
        <input type="password" id="password" name="password" value="<?= esc(get('password')) ?>">
      </div>
      <div>
        <input id="submit" type="submit" name="login" value="είσοδος">
      </div>
    </form>

    <?php if($error): ?>
      <div class="alert">* Δώσατε λάθος email ή κωδικό</div>
    <?php endif ?>
  </main>
</body>
</html>