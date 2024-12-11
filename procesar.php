<?php
session_start();

function conectarDB(){

  $host = "localhost";
  $user = "root";
  $password = "";
  $base_datos = "eventos_deportivos";

  $connection = new mysqli($host, $user, $password, $base_datos);

  if ($connection->connect_error) {
    die("Error de conexión: " . $connection->connect_error);
  }
  return $connection;
}

function get($table){
  $conn = conectarDB();

  if($table === "organizadores"){
    $sql = "SELECT * FROM organizadores";
  }else{
    $sql = "SELECT eventos.*, organizadores.nombre AS nombre_organizador
            FROM eventos
            JOIN organizadores ON eventos.id_organizador = organizadores.id;";
  }
  
  $result = $conn->query($sql);

  $arrayResult = [];
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $arrayResult[] = $row;
    }
  }

  return $arrayResult;
}

function post($accion){
  $conn = conectarDB();
  $table = substr($accion, 4);
  
  if ($table === "organizadores") {

    $regexEmail = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    $regexNumber = '/^[0-9]{9}$/';

    $inputName = isset($_POST['name']) ? trim($_POST['name']) : '';
    $inputEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
    $inputNumber = isset($_POST['number']) ? trim($_POST['number']) : '';

    if (empty($inputName)) {
      $errors[] = "El nombre es obligatorio";
    }else if (strlen($inputName) < 2) {
      $errors[] = "El usuario debe contener mas de 2 caracteres";
    }

    if (empty($inputEmail)) {
      $errors[] = "El correo electronico es obligatorio";
    }else if (!preg_match($regexEmail, $inputEmail)) {
      $errors[] = "El correo debe mantener la estructura de un correo electronico";
    }

    if (empty($inputNumber)) {
      $errors[] = "El numero de telefono es obligatorio";
    }else if (!preg_match($regexNumber, $inputNumber)) {
      $errors[] = "El numero de telefono deve contener 9 digitos";
    }

    if (!empty($errors)) {
      $_SESSION['errors'] = $errors;
      header("Location: pages/organizadores.php");
      $conn->close();
      exit();
      
    }else {

      $columns = "(nombre, email, telefono)";
      $values = [
        htmlspecialchars(trim($inputName)),
        htmlspecialchars(trim($inputEmail)),
        htmlspecialchars(trim($inputNumber)),
      ];
      $types = "sss";
    }

  } else if ($table === "eventos") {

    $inputNombreEvento = isset($_POST['nombre_evento']) ? trim($_POST['nombre_evento']) : '';
    $inputDeporte = isset($_POST['deporte']) ? trim($_POST['deporte']) : '';
    $datetime = isset($_POST['fecha']) ? $_POST['fecha'] : '';
    $inputUbicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
    $idOrganizador = isset($_POST['idOrganizador']) ? $_POST['idOrganizador'] : '';

    if (empty($inputNombreEvento)) {
      $errors[] = "El nombre del evento es obligatorio";
    }else if (strlen($inputNombreEvento) < 2) {
      $errors[] = "El nombre del evento debe contener más de 2 caracteres";
    }

    if (empty($inputDeporte)) {
      $errors[] = "El tipo de deporte es obligatorio";
    }else if (strlen($inputDeporte) < 2) {
      $errors[] = "El tipo de deportedebe contener más de 2 caracteres";
    }

    if (empty($datetime)) {
      $errors[] = "La fecha y hora son obligatorios";
    }

    if (empty($inputUbicacion)) {
      $errors[] = "La ubicación es obligatoria";
    }

    if (empty($idOrganizador) || !is_numeric($idOrganizador)) {
      $errors[] = "El organizador es obligatorio y debe ser un número válido";
    }

    list($date, $time) = explode('T', $datetime);

    if (!empty($errors)) {
      $_SESSION['errors'] = $errors;
      header("Location: pages/eventos.php");
      $conn->close();
      exit();

    } else {

      $columns = "(nombre_evento, tipo_deporte, fecha, hora, ubicacion, id_organizador)";
      $values = [
        htmlspecialchars($inputNombreEvento),
        htmlspecialchars($inputDeporte),
        htmlspecialchars($date),
        htmlspecialchars($time),
        htmlspecialchars($inputUbicacion),
        $idOrganizador,
      ];
      $types = "ssssss";
    }
  }

  $placeholders = implode(", ", array_fill(0, count($values), "?"));
  $stmt = $conn->prepare("INSERT INTO $table $columns VALUES ($placeholders)");
  $stmt->bind_param($types, ...$values);

  if ($stmt->execute()) {
    echo "<script>
            alert('Organizador guardado correctamente.');
            window.location.href = 'pages/$table.php';
          </script>";
    exit();
  } else {
    echo "<script>
            alert('Error al guardar el organizador');
            window.location.href = 'pages/$table.php';
          </script>";
    exit();
  }
}

function put($id){
  $conn = conectarDB();

  $inputNombreEvento = isset($_POST['nombre_evento']) ? trim($_POST['nombre_evento']) : '';
  $inputDeporte = isset($_POST['deporte']) ? trim($_POST['deporte']) : '';
  $datetime = isset($_POST['fecha']) ? $_POST['fecha'] : '';
  $inputUbicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
  $idOrganizador = isset($_POST['idOrganizador']) ? $_POST['idOrganizador'] : '';


  if (empty($inputNombreEvento)) {
    $errors[] = "El nombre del evento es obligatorio";
  }else if (strlen($inputNombreEvento) < 2) {
    $errors[] = "El nombre del evento debe contener más de 2 caracteres";
  }

  if (empty($inputDeporte)) {
    $errors[] = "El tipo de deporte es obligatorio";
  }else if (strlen($inputDeporte) < 2) {
    $errors[] = "El tipo de deportedebe contener más de 2 caracteres";
  }

  if (empty($datetime)) {
    $errors[] = "La fecha y hora son obligatorios";
  }

  if (empty($inputUbicacion)) {
    $errors[] = "La ubicación es obligatoria";
  }

  if (empty($idOrganizador) || !is_numeric($idOrganizador)) {
    $errors[] = "El organizador es obligatorio y debe ser un número válido";
  }

  if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: pages/eventos.php");
    $conn->close();
    exit();
  }
  $id = (int) $id;
  list($date, $time) = explode('T', $datetime);

  
  $stmt = $conn->prepare("UPDATE eventos SET nombre_evento = ?, tipo_deporte = ?, fecha = ?, hora = ?, ubicacion = ?, id_organizador = ? WHERE id = ?");
  $stmt->bind_param("ssssssi", $inputNombreEvento, $inputDeporte, $date, $time, $inputUbicacion, $idOrganizador, $id);

  if ($stmt->execute()) {

    $stmt->close();
    $conn->close();
    echo "<script>
            alert('Evento guardado correctamente');
            window.location.href = './pages/eventos.php';
          </script>";
    exit();
  } else {

    $stmt->close();
    $conn->close();
    echo "<script>
            alert('Se produjo un error');
            window.location.href = './pages/eventos.php';
          </script>";
    exit();
  }
}

function delete($accion){
  $conn = conectarDB();

  $id = $_POST['id'];
  $table = substr($accion, 4);

  if ($table === "organizadores") {

    $stmt = $conn->prepare("SELECT COUNT(*) FROM eventos WHERE id_organizador = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row["COUNT(*)"] > 0) {
      $stmt->close();
      $conn->close();

      echo "<script>
              alert('No se puede eliminar el organizador porque tiene eventos asociados.');
              window.location.href = './pages/organizadores.php';
            </script>";
      exit();
    }
  }
  
  $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    $stmt->close();
    $conn->close();

    echo "<script>
            alert('Eliminado con éxito.');
            window.location.href = './pages/$table.php';
          </script>";
    exit();

  } else {
    $stmt->close();
    $conn->close();
    
    echo "<script>
            alert('Se ha producido un error');
            window.location.href = './pages/$table.php';
          </script>";
    exit();
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $method = substr($_POST['accion'], 0, 4);
  
  switch ($method) {

    case 'POST':
      post($_POST['accion']);
      break;

    case 'UPDA':

      put($_POST['id']);
      break;

    case 'DELT':

      delete($_POST['accion']);
      break;

    default:
        break;
  }
}


