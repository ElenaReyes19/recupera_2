<?php
header("Content-Type","application/json");
$method = $_SERVER["REQUEST_METHOD"];
echo "-".  $method . "-";

switch($method){
    case 'GET': // se hace la consulta ----------------------------------------------------------
        echo " Consultar Registro \n";

        try{
            $conexion = new PDO("mysql:host=localhost;dbname=utez","root",""); // conexion MySQL
        }catch(PDOException $e){
            echo $e->getMessage();
        }

        switch ($_GET['accion']){
            case "persona" :
                echo "- Persona -\n";
                if (isset($_GET['name'])){
                    echo "Registro\n";

                    $rs =  name($conexion);
                    if ($rs != null){
                        echo json_encode($rs, JSON_PRETTY_PRINT);
                    }else{
                        echo "No se encontro el registro";
                    }
                }else{

                    $pstm = $conexion->prepare('SELECT persona.name,personaje.lastname,
                    curp,birthday,docente.num as docente ,
                    estudiante.matri AS estudiante FROM utez.persona inner join
                    docente on docente_num=docente.num inner join estudiante on
                    estudiante_matri = estudiante.matricula ;');
                    $pstm->execute();
                    $rs = $pstm->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($rs, JSON_PRETTY_PRINT);
                }

                break;
            case "Docente" : //docentes ------------------------------------------------
                echo "- Docentes-\n";
                $pstm = $conexion->prepare('SELECT * FROM utez.docente;');
                $pstm->execute();
                $rs = $pstm->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($rs, JSON_PRETTY_PRINT);

                break;
            case "Estudiante " : // estudiantes-----------------------------------------------------------
                echo " estudiantes-\n";
                $pstm = $conexion->prepare('SELECT * FROM utez.estudiante;');
                $pstm->execute();
                $rs = $pstm->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($rs, JSON_PRETTY_PRINT);

                break;
                case "Evaluacion" : //evaluaciones---------------------------------------------------------
                echo " evaluaciones-\n";
                $pstm = $conexion->prepare('SELECT * FROM utez.evaluacion;');
                $pstm->execute();
                $rs = $pstm->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($rs, JSON_PRETTY_PRINT);

                break;
            default:
                echo "No se han encontrado los datos";
                break;
        }
        break;

    case 'POST': // se registra un nuevo usario------------------------------------------
        if($_GET['accion']=='persona'){
            $jsonData = json_decode(file_get_contents("php://input"));
            try{
                $conn = new PDO("mysql:host=localhost;dbname=utez","root","");
            }catch(PDOException $e){
                echo $e->getMessage();
            }

            if (!names($conn,$jsonData)){
                $query = $conn->prepare('INSERT INTO `utez`.`persona`
                (`name`, `lastname`, `curp`, `birthday`, `docente_num`,
                 `estudiante_matri`) VALUES
                 (:name, :lastname, :curp :date, :doc, :estm);');

                $query->bindParam(":name",$jsonData->name);
                $query->bindParam(":lastname",$jsonData->lastname);
                $query->bindParam(":curp",$jsonData->curp);
                $query->bindParam(":date",$jsonData->birthday);
                $query->bindParam(":doc",$jsonData->docnu);
                $query->bindParam(":est",$jsonData->estma);
                $result = $query->execute();
                if($result){
                    $_POST["error"] = false;
                    $_POST["message"] = "Se ha registrado correctamente."; // si se pudo
                    $_POST["status"] = 200;
                }else{
                    $_POST["error"] = true;
                    $_POST["message"] = "Error al registrar"; // :(
                    $_POST["status"] = 400;
                }

                echo json_encode($_POST);
            }else{
                echo "Se ha registrado correctamente \n ";
            }

        }
        break;

    case 'PUT': // se actualiza el registro ----------------------------------------
        echo "Actualizacion de redistro\n";

        if($_GET['accion']=='persona'){
            $jsonData = json_decode(file_get_contents("php://input"));
            try{
                $conn = new PDO("mysql:host=localhost;dbname=utez","root","");
            }catch(PDOException $e){
                echo $e->getMessage();
                if (!names($conn,$jsonData)){

                    $query = $conn->prepare('UPDATE `utez`.`persona` SET `name` = :name,
                    `lastname` = :lastname, `curp` = :curp, `birthday` = :date,
                    `docente_num` = :doc, `estu_matri` = :estm');

                    $query->bindParam(":name",$jsonData->name);
                    $query->bindParam(":lastname",$jsonData->lastname);
                    $query->bindParam(":curp",$jsonData->curp);
                    $query->bindParam(":date",$jsonData->birthday);
                    $query->bindParam(":doc",$jsonData->docente_numemp);
                    $query->bindParam(":estm",$jsonData->estudiante_matri);
                    $result = $query->execute();
                    if($result){
                        $_POST["error"] = false;
                        $_POST["message"] = "Se ha actualizado correctamente ."; // si se pudo
                        $_POST["status"] = 200;
                    }else{
                        $_POST["error"] = true;
                        $_POST["message"] = "Error al actualizar"; //:(
                        $_POST["status"] = 400;
                    }

                    echo json_encode($_POST);
                }else{
                    echo "Ya existe el registro\n "; // si funciono
                }

            }else{
                echo "No se ha encontrado el registro"; // raro pero tambien funciono
            }

        }

        break;

}

function name($conexion ){ //la coneccion-----------------------------------------------
    $pstm = $conexion->prepare('SELECT persona.name,lastname,curp,birthday,
    docente.num as docente , estudiante.matri AS estudiante
    FROM utez.persona inner join docente on
    docente_num=docente.num inner join estudiante
    on  estu_matri = estudiante.matri');
    $pstm->bindParam(":name",$_GET['name'] );
    $pstm->execute();
    $rs = $pstm->fetchAll(PDO::FETCH_ASSOC);
    return $rs;
}



function names($conn, $json ){
    $pstm = $conn->prepare('SELECT * FROM utez.persona
    WHERE `name`  = :name AND `lastname` = :lastname '
    AND `curp`  = :curp);
    $pstm->bindParam(":name",$json->name );
    $pstm->bindParam(":lastname",$json->lastname );
    $pstm->bindParam(":curp",$json->curp );
    $pstm->execute();
    $rs = $pstm->fetchAll(PDO::FETCH_ASSOC);
    return $rs != null ;
}

//minimo se intento :(