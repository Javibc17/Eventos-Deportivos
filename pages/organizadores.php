<?php
  include "../procesar.php";

  $errors = isset($_SESSION['errors']) ? $_SESSION['errors']: null ;
  unset($_SESSION['errors']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organizadores</title>
  <link rel="stylesheet" href="../styles/crudStyle.css">
</head>
<body>
  <header>
    <h1>Organizadores</h1>
  </header>
  <main>
    <div>
      <table>
        <thead>
          <th>Id</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Telefono</th>
          <th></th>
        </thead>
        <tbody>
          <?php 
            $managers = get("organizadores");

            if (empty($managers)) {
              echo "<tr><td colspan='5'>No se ha encontrado ningún usuario</td></tr>";
            } else {
              foreach ($managers as $manager):
          ?>
            <tr>
              <td><?php echo $manager['id']; ?></td>
              <td><?php echo $manager['nombre']; ?></td>
              <td><?php echo $manager['email']; ?></td>
              <td><?php echo $manager['telefono']; ?></td>
              <td>
								<form action='../procesar.php' method='POST'>
									<input type='hidden' name='accion' value='DELTorganizadores'>
									<input type='hidden' name='id' value='<?php echo $manager['id']; ?>'>
									<button type='submit' onclick='return confirm("¿Estás seguro de que deseas eliminar este organizador?")'>Eliminar</button>
								</form>
              </td>
            </tr>
          <?php 
              endforeach; 
            }
          ?>

        </tbody>
      </table>
    </div>

    <form action="../procesar.php" method="post">
      
      <?php if ($errors == true): ?>
        <ul id="errorList">
          <?php 
            foreach ($errors as $error) {
              echo "<li>$error</li>";
            }
          ?>
        </ul>
      <?php endif; ?>
      
      <div>
        <h2>Crear organizador</h2>
        <input type="hidden" name="accion" value="POSTorganizadores">

        <input type="text" id="name" name="name" placeholder="Nombre">
        <input type="email" id="email" name="email" placeholder="Correo">
        <input type="text" id="number" name="number" placeholder="Telefono">
      </div>

      <button id="btnForm" type="submit">Crear</button>
    </form>
  </main>

</body>
</html>