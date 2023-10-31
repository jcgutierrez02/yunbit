<?php
   
    session_start();
   
    if (!isset($_SESSION['nombre']))
       header('Location: login.php');
    else {
       $nombre = $_SESSION['nombre'];
       $apellidos = $_SESSION['apellidos'];
       
    }

    // Recoger lo que llega
    $id = $_GET['id'];
    
    $detalle = obtener_detalle($id);

    // Ruta al archivo PDF o URL del PDF
    $archivo_img = "img/" .  $detalle['imagen'];

    // Verificar si el archivo existe
    if (file_exists($archivo_img)) {
        
        // Obtenemos el tipo de contenido de la imagen
        $tipo_contenido = mime_content_type($archivo_img);

        // Establecemos las cabeceras para mostrar la imagen
        header("Content-Type: $tipo_contenido");

        // Leemos el archivo y lo mostramos en el navegador
        ob_clean();
        flush();
        readfile($archivo_img);

    } else {
        // Si el archivo no existe, mostrar un mensaje de error
        header("HTTP/1.0 404 Not Found");
        echo 'El archivo PDF no existe.';
    }

    function obtener_detalle($id) {
    
        include_once 'claseConexionBD.php';

        $BD = new ConectarBD();   
        $conn = $BD->getConexion();

        $stmt = $conn->prepare('SELECT * FROM test_clients WHERE id = :id');
        $stmt->execute(array(':id' => $id));  
        $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

        $BD->cerrarConexion();

        return $detalle;   
    }
    
?>

