<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsOperaciones {

    // Registrar un préstamo (Accion = 1) o devolución (Accion = 2)
    public function registrarOperacion($idUsuarioOperador, $idUsuarioDestino, $idEquipo, $accion, $observaciones = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idUsuarioOperador = (int)$idUsuarioOperador;
        $idUsuarioDestino = (int)$idUsuarioDestino;
        $idEquipo = mysqli_real_escape_string($link, trim($idEquipo));
        $accion = (int)$accion;
        $fechaHoy = date('Y-m-d');
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));

        // Insertamos en operaciones (nota: guardamos el IDUsuario del cliente/destinatario)
        $sql = "INSERT INTO operaciones (Accion, Fecha, Observaciones, IDUsuario, IDEquipo) 
                VALUES ($accion, '$fechaHoy', '$observaciones', $idUsuarioDestino, '$idEquipo')";

        return mysqli_query($link, $sql);
    }

    // Obtener lista de usuarios activos para el selector de préstamos directos
    public function obtenerUsuariosActivos() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $sql = "SELECT IDUsuario, Nombre, email FROM usuario WHERE activo = 1 ORDER BY Nombre ASC";
        $resultado = mysqli_query($link, $sql);
        $usuarios = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }

    // Convertir una reserva existente en préstamo (Accion = 1) usando los nuevos campos de horario en operaciones
    public function convertirReservaEnPrestamo($idReserva, $idUsuarioOperador) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idReserva = (int)$idReserva;
        $fechaHoy = date('Y-m-d');

        $sqlReserva = "SELECT IDUsuario, HoraInicio, HoraFin FROM reservas WHERE IDReservas = $idReserva AND Fecha = '$fechaHoy' AND activo = 1";
        $resR = mysqli_query($link, $sqlReserva);
        
        if (!$resR || mysqli_num_rows($resR) === 0) {
            return $this->generarModalRespuesta("Error", "La reserva no existe, no corresponde al día de hoy o ya fue procesada.", "bg-danger text-white", "btn-danger");
        }

        $datosReserva = mysqli_fetch_assoc($resR);
        $idUsuarioCliente = $datosReserva['IDUsuario'];
        $horaInicioRes = $datosReserva['HoraInicio'];
        $horaFinRes = $datosReserva['HoraFin'];

        $sqlEquipos = "SELECT IDEquipo FROM tiene_equipos WHERE IDReservas = $idReserva";
        $resEq = mysqli_query($link, $sqlEquipos);
        
        if (!$resEq || mysqli_num_rows($resEq) === 0) {
            return $this->generarModalRespuesta("Atención", "La reserva no tiene equipos asociados.", "bg-warning text-dark", "btn-warning");
        }

        while ($eqCheck = mysqli_fetch_assoc($resEq)) {
            if ($this->estaEquipoPrestado($eqCheck['IDEquipo'])) {
                return $this->generarModalRespuesta("Equipo Ocupado", "El equipo con ID " . $eqCheck['IDEquipo'] . " ya se encuentra prestado actualmente.", "bg-danger text-white", "btn-danger");
            }
        }

        $resEq = mysqli_query($link, $sqlEquipos);

        mysqli_begin_transaction($link);

        try {
            while ($eq = mysqli_fetch_assoc($resEq)) {
                $idEquipo = $eq['IDEquipo'];
                $sqlOp = "INSERT INTO operaciones (Accion, Fecha, HoraInicio, HoraFin, Observaciones, IDUsuario, IDEquipo) 
                          VALUES (1, '$fechaHoy', '$horaInicioRes', '$horaFinRes', 'Préstamo generado desde Reserva #$idReserva', $idUsuarioCliente, '$idEquipo')";
                if (!mysqli_query($link, $sqlOp)) {
                    throw new Exception("Error al registrar el préstamo del equipo $idEquipo.");
                }
            }

            $sqlBajaReserva = "UPDATE reservas SET activo = 0 WHERE IDReservas = $idReserva";
            if (!mysqli_query($link, $sqlBajaReserva)) {
                throw new Exception("Error al dar de baja la reserva.");
            }

            mysqli_commit($link);
            return $this->generarModalRespuesta("¡Préstamo Registrado!", "La reserva fue convertida en préstamo activo exitosamente.", "bg-success text-white", "btn-success", true);

        } catch (Exception $e) {
            mysqli_rollback($link);
            return $this->generarModalRespuesta("Error", $e->getMessage(), "bg-danger text-white", "btn-danger");
        }
    }

    // Registrar un préstamo directo (sin reserva previa) guardando los horarios directamente en operaciones
    public function registrarPrestamoDirecto($idUsuarioCliente, $horaInicio, $horaFin, $arrayEquipos) {
        if (empty($arrayEquipos) || !is_array($arrayEquipos)) {
            return $this->generarModalRespuesta("Atención", "Debe seleccionar al menos un equipo para realizar el préstamo.", "bg-warning text-dark", "btn-warning");
        }

        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $fechaHoy = date('Y-m-d');
        $idUsuarioCliente = (int)$idUsuarioCliente;
        $horaInicio = mysqli_real_escape_string($link, trim($horaInicio));
        $horaFin = mysqli_real_escape_string($link, trim($horaFin));

        if ($horaInicio >= $horaFin) {
            return $this->generarModalRespuesta("Error de Horario", "El horario de inicio debe ser menor al horario de fin.", "bg-danger text-white", "btn-danger");
        }

        foreach ($arrayEquipos as $idEquipo) {
            if ($this->estaEquipoPrestado($idEquipo)) {
                return $this->generarModalRespuesta("Equipo Ocupado", "El equipo con ID $idEquipo ya se encuentra prestado actualmente.", "bg-danger text-white", "btn-danger");
            }
        }

        mysqli_begin_transaction($link);

        try {
            // Se inserta directamente en operaciones con su hora de inicio y fin, sin tocar la tabla reservas
            foreach ($arrayEquipos as $idEquipo) {
                $idEquipoEscaped = mysqli_real_escape_string($link, trim($idEquipo));

                $sqlOp = "INSERT INTO operaciones (Accion, Fecha, HoraInicio, HoraFin, Observaciones, IDUsuario, IDEquipo) 
                          VALUES (1, '$fechaHoy', '$horaInicio', '$horaFin', 'Préstamo directo sin reserva previa', $idUsuarioCliente, '$idEquipoEscaped')";
                if (!mysqli_query($link, $sqlOp)) {
                    throw new Exception("Error al registrar la operación de préstamo para el equipo $idEquipoEscaped.");
                }
            }

            mysqli_commit($link);
            return $this->generarModalRespuesta("¡Préstamo Directo Exitoso!", "El préstamo se ha registrado correctamente.", "bg-success text-white", "btn-success", true);

        } catch (Exception $e) {
            mysqli_rollback($link);
            return $this->generarModalRespuesta("Error", $e->getMessage(), "bg-danger text-white", "btn-danger");
        }
    }
    // Obtener reservas del día actual
    public function obtenerReservasDelDia($fecha) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $fecha = mysqli_real_escape_string($link, $fecha);

        $sql = "
            SELECT r.IDReservas, r.HoraInicio, r.HoraFin, r.IDUsuario, 
                   u.Nombre AS nombre_usuario, te.IDEquipo, e.Tipo
            FROM reservas r
            JOIN usuario u ON r.IDUsuario = u.IDUsuario
            JOIN tiene_equipos te ON r.IDReservas = te.IDReservas
            JOIN equipo e ON te.IDEquipo = e.IDEquipo
            WHERE r.activo = 1 AND r.Fecha = '$fecha'
            ORDER BY r.HoraInicio ASC;
        ";

        $resultado = mysqli_query($link, $sql);
        $reservas = [];

        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $idRes = $row['IDReservas'];
                if (!isset($reservas[$idRes])) {
                    $reservas[$idRes] = [
                        'IDReservas' => $row['IDReservas'],
                        'HoraInicio' => $row['HoraInicio'],
                        'HoraFin' => $row['HoraFin'],
                        'IDUsuario' => $row['IDUsuario'],
                        'nombre_usuario' => $row['nombre_usuario'],
                        'equipos' => []
                    ];
                }
                $reservas[$idRes]['equipos'][] = [
                    'IDEquipo' => $row['IDEquipo'],
                    'Tipo' => $row['Tipo']
                ];
            }
        }
        return $reservas;
    }
    // Helper privado para generar modales estandarizados
    private function generarModalRespuesta($titulo, $mensaje, $claseHeader, $claseBoton, $recargar = false) {
        $onclick = $recargar ? 'onclick="location.reload();"' : '';
        return '
        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header ' . $claseHeader . '">
                        <h5 class="modal-title fw-bold">' . $titulo . '</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 text-dark mb-0">' . $mensaje . '</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn ' . $claseBoton . ' px-4 fw-bold" ' . $onclick . ' data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>';
    }
    // Verifica si un equipo se encuentra actualmente prestado (última acción = 1 sin devolución posterior)
    public function estaEquipoPrestado($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idEquipoEscaped = mysqli_real_escape_string($link, trim($idEquipo));

        $sql = "SELECT Accion FROM operaciones WHERE IDEquipo = '$idEquipoEscaped' ORDER BY IDOperaciones DESC LIMIT 1";
        $resultado = mysqli_query($link, $sql);

        if ($resultado && mysqli_num_rows($resultado) > 0) {
            $row = mysqli_fetch_assoc($resultado);
            // Si la última operación es Préstamo (1), está prestado actualmente
            if ((int)$row['Accion'] === 1) {
                return true;
            }
        }
        return false;
    }
}
?>