<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsOperaciones {

    //Registrar un préstamo (accion = 1) o devolución (accion = 2)
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
    //Obtengo la lista de usuarios activos para el selector de préstamos directos
    public function obtenerUsuariosActivos() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $sql = "SELECT IDUsuario, Nombre, email, COALESCE(Faltas, 0) AS Faltas FROM usuario ORDER BY Nombre ASC";
        $resultado = mysqli_query($link, $sql);
        $usuarios = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $usuarios[] = $row;
            }
        }
        return $usuarios;
    }
    // Convierto una reserva existente en préstamo (accion = 1) 
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
    // Registro un préstamo directo (sin reserva previa) guardando los horarios directamente en operaciones
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
            //Inserto directamente en operaciones con su hora de inicio y fin
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
    //Obtener reservas del día actual
    public function obtenerReservasDelDia($fecha) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $fecha = mysqli_real_escape_string($link, $fecha);

        //Consulta que trae la reserva, el usuario responsable y sus faltas
        $sql = "
            SELECT r.IDReservas, r.Fecha, r.HoraInicio, r.HoraFin, 
                   u.IDUsuario, u.Nombre AS nombre_usuario, u.email, COALESCE(u.Faltas, 0) AS Faltas
            FROM reservas r
            JOIN usuario u ON r.IDUsuario = u.IDUsuario
            WHERE r.Fecha = '$fecha' AND r.activo = 1
            ORDER BY r.HoraInicio ASC;
        ";

        $resultado = mysqli_query($link, $sql);
        $reservas = [];

        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $idReserva = $row['IDReservas'];
                //Obtener equipos de la reserva
                $sqlEq = "SELECT te.IDEquipo, e.Tipo FROM tiene_equipos te JOIN equipo e ON te.IDEquipo = e.IDEquipo WHERE te.IDReservas = $idReserva";
                $resEq = mysqli_query($link, $sqlEq);
                $equipos = [];
                if ($resEq) {
                    while ($eqRow = mysqli_fetch_assoc($resEq)) {
                        $equipos[] = $eqRow;
                    }
                }
                $row['equipos'] = $equipos;
                $reservas[] = $row;
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
    // Obtener la lista de equipos que se encuentran actualmente prestados con su respectivo usuario responsable
    public function obtenerEquiposPrestadosActualmente() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        // Buscamos los equipos cuya última operación registrada sea un Préstamo (Accion = 1)
        $sql = "
            SELECT o.IDOperaciones, o.IDEquipo, e.Tipo, e.Estado as EstadoEquipo, 
                   o.IDUsuario, u.Nombre AS NombreUsuario, u.email, o.Fecha, o.HoraInicio, o.HoraFin
            FROM operaciones o
            JOIN equipo e ON o.IDEquipo = e.IDEquipo
            JOIN usuario u ON o.IDUsuario = u.IDUsuario
            WHERE o.IDOperaciones = (
                SELECT MAX(o2.IDOperaciones) 
                FROM operaciones o2 
                WHERE o2.IDEquipo = o.IDEquipo
            )
            AND o.Accion = 1
            ORDER BY o.Fecha DESC, o.HoraInicio DESC;
        ";

        $resultado = mysqli_query($link, $sql);
        $prestados = [];

        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $prestados[] = $row;
            }
        }
        return $prestados;
    }

    // Registrar la devolución de un equipo (Accion = 2) actualizando estado, disponibilidad y observaciones del equipo
    public function registrarDevolucion($idEquipo, $estadoDevolucion, $nuevoEstadoEquipo, $observacionesDevolucion, $idUsuarioResponsable, $aplicarFalta = false) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $idEquipo = mysqli_real_escape_string($link, trim($idEquipo));
        $estadoDevolucion = mysqli_real_escape_string($link, trim($estadoDevolucion)); 
        $nuevoEstadoEquipo = mysqli_real_escape_string($link, trim($nuevoEstadoEquipo)); 
        $observacionesDevolucion = mysqli_real_escape_string($link, trim($observacionesDevolucion));
        $idUsuarioResponsable = (int)$idUsuarioResponsable;
        $aplicarFalta = (bool)$aplicarFalta;
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');

        // Validar que el equipo esté prestado
        if (!$this->estaEquipoPrestado($idEquipo)) {
            return $this->generarModalRespuesta("Atención", "El equipo seleccionado no figura como prestado actualmente.", "bg-warning text-dark", "btn-warning");
        }

        // Regla de negocio: Si está roto, NUNCA puede quedar en estado Disponible
        if ($estadoDevolucion === 'roto' && $nuevoEstadoEquipo === 'Disponible') {
            return $this->generarModalRespuesta("Error de Validación", "Un equipo reportado como roto o dañado no puede quedar en estado Disponible.", "bg-danger text-white", "btn-danger");
        }

        mysqli_begin_transaction($link);

        try {
            // 1. Registrar la operación de Devolución (Accion = 2)
            $obsFinal = "Devolución registrada. Condición: " . ucfirst($estadoDevolucion) . ". " . $observacionesDevolucion;
            $sqlOp = "INSERT INTO operaciones (Accion, Fecha, HoraInicio, HoraFin, Observaciones, IDUsuario, IDEquipo) 
                      VALUES (2, '$fechaHoy', '$horaActual', '$horaActual', '$obsFinal', $idUsuarioResponsable, '$idEquipo')";
            
            if (!mysqli_query($link, $sqlOp)) {
                throw new Exception("Error al registrar la operación de devolución.");
            }

            // 2. Actualizar el estado, la disponibilidad Y las observaciones directamente en la tabla equipo
            // Nota: Si prefieres conservar las observaciones anteriores y concatenar, puedes usar CONCAT, 
            // pero actualizar el campo directamente refleja el parte DOT más reciente de la devolución.
            $disponibilidadEquipo = ($estadoDevolucion === 'normal') ? 1 : 0;
            
            // Si el usuario no escribió observaciones, podemos mantener o limpiar, pero aseguramos registrar el texto ingresado
            $obsEquipoEscaped = !empty($observacionesDevolucion) ? $observacionesDevolucion : "Devolución en estado " . $estadoDevolucion;
            
            $sqlEquipo = "UPDATE equipo 
                          SET Estado = '$nuevoEstadoEquipo', 
                              Disponibilidad = $disponibilidadEquipo, 
                              Observaciones = '$obsEquipoEscaped' 
                          WHERE IDEquipo = '$idEquipo'";
            
            if (!mysqli_query($link, $sqlEquipo)) {
                throw new Exception("Error al actualizar el estado físico y observaciones del equipo.");
            }

            // 3. Registrar falta si corresponde (Usando COALESCE para evitar problemas con NULL)
            $textoFalta = "";
            if ($estadoDevolucion === 'roto' && $aplicarFalta) {
                $sqlFalta = "UPDATE usuario SET Faltas = COALESCE(Faltas, 0) + 1 WHERE IDUsuario = $idUsuarioResponsable";
                if (!mysqli_query($link, $sqlFalta)) {
                    throw new Exception("Error al registrar la falta en el historial del usuario.");
                }
                $textoFalta = " y se aplicó 1 falta al historial del usuario.";
            } elseif ($estadoDevolucion === 'roto') {
                $textoFalta = " (sin sanción de falta por decisión del operador).";
            }

            mysqli_commit($link);
            
            $mensajeExito = "Devolución registrada exitosamente. El equipo pasó a estado '$nuevoEstadoEquipo'" . $textoFalta;
            return $this->generarModalRespuesta("¡Devolución Exitosa!", $mensajeExito, "bg-success text-white", "btn-success", true);

        } catch (Exception $e) {
            mysqli_rollback($link);
            return $this->generarModalRespuesta("Error", $e->getMessage(), "bg-danger text-white", "btn-danger");
        }
    }
    // Registrar que un equipo no fue devuelto, pasarlo a No Disponible y aplicar falta obligatoria
    public function marcarEquipoNoDevuelto($idEquipo, $idUsuarioResponsable, $observaciones = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $idEquipo = mysqli_real_escape_string($link, trim($idEquipo));
        $idUsuarioResponsable = (int)$idUsuarioResponsable;
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $fechaHoy = date('Y-m-d');
        $horaActual = date('H:i:s');

        // Validar que el equipo esté prestado
        if (!$this->estaEquipoPrestado($idEquipo)) {
            return $this->generarModalRespuesta("Atención", "El equipo seleccionado no figura como prestado actualmente.", "bg-warning text-dark", "btn-warning");
        }

        mysqli_begin_transaction($link);

        try {
            // 1. Registrar la operación de cierre por falta de devolución (Accion = 2 con nota aclaratoria)
            $obsFinal = "Incidencia: Equipo no devuelto al cierre del día. " . ($observaciones ? $observaciones : "Sin observaciones.");
            $sqlOp = "INSERT INTO operaciones (Accion, Fecha, HoraInicio, HoraFin, Observaciones, IDUsuario, IDEquipo) 
                      VALUES (2, '$fechaHoy', '$horaActual', '$horaActual', '$obsFinal', $idUsuarioResponsable, '$idEquipo')";
            
            if (!mysqli_query($link, $sqlOp)) {
                throw new Exception("Error al registrar la operación.");
            }

            // 2. Actualizar el estado del equipo a NoDisponible y Disponibilidad = 0
            $sqlEquipo = "UPDATE equipo 
                          SET Estado = 'NoDisponible', 
                              Disponibilidad = 0, 
                              Observaciones = 'No devuelto el $fechaHoy. $observaciones' 
                          WHERE IDEquipo = '$idEquipo'";
            
            if (!mysqli_query($link, $sqlEquipo)) {
                throw new Exception("Error al actualizar el estado físico del equipo.");
            }

            // 3. Aplicar sanción obligatoria de +1 falta al usuario (usando COALESCE por seguridad)
            $sqlFalta = "UPDATE usuario SET Faltas = COALESCE(Faltas, 0) + 1 WHERE IDUsuario = $idUsuarioResponsable";
            if (!mysqli_query($link, $sqlFalta)) {
                throw new Exception("Error al registrar la falta en el historial del usuario.");
            }

            mysqli_commit($link);
            
            return $this->generarModalRespuesta("Registro Actualizado", "El equipo fue marcado como no devuelto, su estado pasó a 'No Disponible' y se aplicó 1 falta al usuario responsable.", "bg-danger text-white", "btn-danger", true);

        } catch (Exception $e) {
            mysqli_rollback($link);
            return $this->generarModalRespuesta("Error", $e->getMessage(), "bg-danger text-white", "btn-danger");
        }
    }
    public function cancelarReservaPorDot($idReserva) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idReserva = (int)$idReserva;

        $sql = "UPDATE reservas SET activo = 0 WHERE IDReservas = $idReserva";
        if (mysqli_query($link, $sql)) {
            return $this->generarModalRespuesta("Reserva Cancelada", "La reserva ha sido cancelada exitosamente por el DOT.", "bg-danger text-white", "btn-danger", true);
        } else {
            return $this->generarModalRespuesta("Error", "No se pudo cancelar la reserva en este momento.", "bg-danger text-white", "btn-danger");
        }
    }
}
?>