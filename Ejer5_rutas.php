<?php
    session_start();
   
    if (!isset($_SESSION['nombre']))
      header('Location: login.php');
    
    $mensajeOK = "Introducir todos los datos";  // Para mostrar en formulario

    // Para mostrarlos en la página 
    $nombre = $_SESSION['nombre'];
    $apellidos = $_SESSION['apellidos'];  
    $rol = $_SESSION['rol']; 

    $clientes = obtener_datos(); 

    if ( isset($_POST['enviar']) ) {
        insertar_datos($_POST, $_FILES, $mensajeOK);
       // unset($_POST['enviar']);  // Para que no se recargue automáticamente 
                                  // si no se ha pulsado el botón 
    }
    
    function obtener_datos() {
    
        include_once 'claseConexionBD.php';
    
        $BD = new ConectarBD();   
        $conn = $BD->getConexion();
       
        $stmt = $conn->prepare('SELECT * FROM test_clients');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);   
        $stmt->execute();
        $datos = $stmt->fetchAll();
        
        $BD->cerrarConexion();
        
        return $datos;   
    }
    
    function insertar_datos($datos, $fichero, &$mensajeOK) {
        
        if (is_uploaded_file($_FILES["imagen"]["tmp_name"])) // La imagen se ha subido al servidor
        { 
            $tamanio = $_FILES["imagen"]["size"];
            $nom_archivo = $_FILES["imagen"]["name"];
            $ruta_archivo = "img/" . $nom_archivo; 
    
            if ( $tamanio > 10024 )  // Tamaño superior a 10Mb
                $mensajeOK = "El tamaño del archivo es $tamanio, superior a los 10Mb permitidos";
            else {
                
                // Leer imagen en formato binario
                $fp = fopen($_FILES["imagen"]["tmp_name"], 'rb');
    
                // Subir fichero imagen a la carpeta definitiva servidor 
                move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo);
    
                include_once 'claseConexionBD.php';
    
                $BD = new ConectarBD();   
                $conn = $BD->getConexion();
        
                $stmt = $conn->prepare('INSERT INTO test_clients (id, name, address, description, tlf, type, imagen, imagen2) '
                    . 'VALUES (0, :name, :address, :description, :telf, :type, :imagen, :imagen2)');
            
                try {    
                    $stmt->bindParam(':name', $datos['nombre']);
                    $stmt->bindParam(':address', $datos['direccion']);
                    $stmt->bindParam(':description', $datos['descripcion']);
                    $stmt->bindParam(':telf', $datos['telf']);
                    $stmt->bindParam(':type', $datos['tipo']);
                    $stmt->bindParam(':imagen', $nom_archivo);
                    $stmt->bindParam(':imagen2', $fp, PDO::PARAM_LOB);
    
                    $stmt->execute();
    
                    fclose($fp);
                   
                    $mensajeOK = 'Cliente dado de alta';
    
                }
                catch (PDOException $ex) {
                    print "¡Error!: " . $ex->getMessage() . "<br/>";
                    die();
                }
                $BD->cerrarConexion();
            } // Fin else
        } // Fin upload
        else
            $mensajeOK = 'No se pudo subir la imagen al servidor.';
    }    

?>


<!DOCTYPE html>

<html>
    <head>
        <title>Inserción con rutas</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel='stylesheet' type='text/css' href='css/estilos_2.css' />           
    </head>
    <body>
    <?php echo 'Conectado como <b>' . $nombre . ' ' . $apellidos ?>
    <p><a href="desconectar.php">Cerrar Sesión</a></p>
    <?php 
       if ($rol == 'A') // es un administrador => mostrar formulario de inserción
       {
    ?>    
        <div class="contacto">
            <form action="" method="post" enctype="multipart/form-data" 
                                onsubmit="return validarForm();"> 
                <label>Nombre: </label><input type="text" name="nombre" id="nombre"/>
                <label>Dirección: </label><input type="text" name="direccion" id="direccion"/><br/>
                <label>Descripción: </label><input type="text" name="descripcion" id="descripcion"/>
                <label>Teléfono: </label><input type="text" name="telf" id="telf"/><br/>
                <label>Tipo: </label><input type="text" name="tipo" id="tipo"/><br/>
                <label>Imagen: </label>
                <input type="file" name="imagen" id="imagen" /><br/><br/>
                
                <input type="submit" name="enviar" value="Nuevo Cliente" /><br/><br/> 
                
            </form>
            <p style="color: green; font-weight: bold"><?php echo $mensajeOK; ?></p><br/>

        </div>    

    <?php
       }
        if ( count($clientes) > 0 ) {
            echo '<table>';
            echo '<tr><th>Nombre</th><th>Dirección</th><th>Teléfono</th><th>imagen</th><th>img_blob</th><th>Acciones</th></tr>'; 
        
            foreach ( $clientes as $cliente ) {
            
                if ( $cliente['type'] == 'P' )
                    echo '<tr style="font-weight: bold">';
                else
                    echo '<tr>';  

                // Convertir la imagen BLOB en base64
                $imagen_base64 = base64_encode($cliente['imagen2']);
            
                echo "<td>".$cliente['name']."</td><td>".$cliente['address'].
                    "</td><td>".$cliente['tlf'].

                    "<td><img src='img/" . $cliente['imagen'] . "' height=50px/></td>" .
                
                    "<td><img src='data:image/jpeg;base64," . $imagen_base64 . "' height=50px/></td>";
                
                    /*
                    * Lo siguiente es la la ruta amigable que es del estilo 
                    * cadena/número. Esta ruta amigable tiene su correspondencia
                    * real definida en el archivo .htaccess y apunta a detalle.php?id=numero
                    */      
            
                echo "<td><a href='producto/". $cliente['id']. "'>detalle</a>
                        <a href='descarga/". $cliente['id']. "'>descarga</a>
                        <a href='visualiza/". $cliente['id']. "'>visualiza</a></td>";                       

                echo "</tr>";
            }    

            echo "</table><br/>";

            echo 'Número de clientes: ' . count($clientes); 
        }
        else
            echo 'No hay clientes';
    ?>          
    
        <br/>
     
        <script type="text/javascript" src="js/funciones.js"></script> 
    </body>
</html> 
