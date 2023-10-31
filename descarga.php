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
        // Configurar las cabeceras HTTP para la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($archivo_img) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($archivo_img));

        // Leer el archivo y enviarlo al navegador
        ob_clean();
        flush();
        readfile($archivo_img);
     //   exit;
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

