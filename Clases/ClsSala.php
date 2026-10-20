<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsSala {

    public function registrarSala($idSalon, $observaciones, $disponibilidad) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idSalon = mysqli_real_escape_string($link, trim($idSalon));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $disponibilidad = mysqli_real_escape_string($link, trim($disponibilidad));

        $sqlCheck = "SELECT IDSalon FROM sala_ceilab WHERE IDSalon = '$idSalon'";
        $resultadoCheck = mysqli_query($link, $sqlCheck);
        if ($resultadoCheck && mysqli_num_rows($resultadoCheck) > 0) {
            return false; // Ya existe, retornamos falso para que muestre el mensaje de error
        }

        //Inserción protegiendo con try-catch
        try {
            $sql = "INSERT INTO sala_ceilab (IDSalon, Observaciones, Disponibilidad) VALUES ('$idSalon', '$observaciones', '$disponibilidad')";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
    public function listarSalas() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "SELECT IDSalon, Observaciones, Disponibilidad FROM sala_ceilab";
        $resultado = mysqli_query($link, $sql);
        
        $salas = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $salas[] = $row;
            }
        }
        return $salas;
    }

    public function eliminarSala($idSalon) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idSalon = mysqli_real_escape_string($link, $idSalon);

        //Verificar si hay equipos asociados a esta sala
        $sqlCheck = "SELECT COUNT(*) as total FROM equipo WHERE IDSala_ceilab = '$idSalon'";
        $resCheck = mysqli_query($link, $sqlCheck);
        if ($resCheck) {
            $row = mysqli_fetch_assoc($resCheck);
            if ($row['total'] > 0) {
                return "foreign_key"; //  Tiene equipos asociados
            }
        }

        try {
            $sql = "DELETE FROM sala_ceilab WHERE IDSalon = '$idSalon'";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }

    public function obtenerSalaPorId($idSalon) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idSalon = mysqli_real_escape_string($link, $idSalon);
        $sql = "SELECT IDSalon, Observaciones, Disponibilidad FROM sala_ceilab WHERE IDSalon = '$idSalon'";
        $resultado = mysqli_query($link, $sql);

        if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
            return $row;
        }
        return null;
    }

    public function modificarSala($idOriginal, $nuevoId, $observaciones, $disponibilidad) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idOriginal = mysqli_real_escape_string($link, trim($idOriginal));
        $nuevoId = mysqli_real_escape_string($link, trim($nuevoId));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $disponibilidad = mysqli_real_escape_string($link, trim($disponibilidad));

        if ($idOriginal !== $nuevoId) {
            $sqlCheck = "SELECT IDSalon FROM sala_ceilab WHERE IDSalon = '$nuevoId'";
            $resultadoCheck = mysqli_query($link, $sqlCheck);
            if ($resultadoCheck && mysqli_num_rows($resultadoCheck) > 0) {
                return false; // El nuevo ID ya está en uso
            }
        }

        try {
            if ($idOriginal !== $nuevoId) {
                $sql = "UPDATE sala_ceilab SET IDSalon = '$nuevoId', Observaciones = '$observaciones', Disponibilidad = '$disponibilidad' WHERE IDSalon = '$idOriginal'";
            } else {
                $sql = "UPDATE sala_ceilab SET Observaciones = '$observaciones', Disponibilidad = '$disponibilidad' WHERE IDSalon = '$idOriginal'";
            }

            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}    
?>