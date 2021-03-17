<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cosmolights - Results</title>

  <style>
    html {
      font-size: 1.2rem;
      font-family: Arial, sans-serif;
      padding: 1rem;
    }

    table {
      border-collapse: collapse;
    }

    td {
      border: 1px solid black;
      padding: 0.5em 3em 0.5em 0.5rem;
    }
  </style>
</head>
<body>
 <h1><?= $page->title() ?></h1>

 <table>
  <tr>
    <th align="left">Θέση</th>
    <th align="left">id</th>
    <th align="left">Video</th>
    <th align="left">Ψήφοι</th>
    <th align="left">Βαθμολογία</th>
  </tr>
  <?php foreach ($results as $key => $result): ?>
    <tr>
      <td><?= $key + 1 ?></td>
      <td><?= $result->id ?></td>
      <td><?= $result->video ?></td>
      <td><?= $result->votes ?></td>
      <td><?= $result->score ?></td>
    </tr>
  <?php endforeach ?>
 </table>

 <p>Άτομα που ψήφισαν (χωρίς τους κριτές): <?= $voted ?></p>
</body>
</html>