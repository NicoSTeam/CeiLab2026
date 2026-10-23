<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsReservas {

    public function verificarDisponibilidadSala($idSalon, $fecha, $horaInicio, $horaFin) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idSalon = mysqli_real_escape_string($link, trim($idSalon));
        $fecha = mysqli_real_escape_string($link, trim($fecha));
        $horaInicio = mysqli_real_escape_string($link, trim($horaInicio));
        $horaFin = mysqli_real_escape_string($link, trim($horaFin));

        $sql = "SELECT COUNT(*) as total FROM reservas 
                WHERE IDSalon = '$idSalon' 
                AND Fecha = '$fecha' 
                AND activo = 1
                AND ('$horaInicio' < HoraFin AND '$horaFin' > HoraInicio)";

        $resultado = mysqli_query($link, $sql);
        if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
            return ($row['total'] == 0); // True si está libre
        }

        return false;
    }
    public function registrarReservaSala() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                // Recuperamos el ID del usuario logueado en sesión
                $idUsuario = $_SESSION['IDUsuario'] ?? $_SESSION['id_usuario'] ?? 1; 

                $idSalon = $_POST['idsala'] ?? '';
                $fecha = $_POST['fecha'] ?? '';
                $horaInicio = $_POST['hora_inicio'] ?? '';
                $horaFin = $_POST['hora_fin'] ?? '';
                
                // Calculamos cantidad de horas 
                $cantHoras = (strtotime($horaFin) - strtotime($horaInicio)) / 3600;
                if ($cantHoras <= 0) $cantHoras = 1;

                $objConexion = new Conexion();
                $link = $objConexion->conectar();

                $idSalon = mysqli_real_escape_string($link, trim($idSalon));
                $fecha = mysqli_real_escape_string($link, trim($fecha));
                $horaInicio = mysqli_real_escape_string($link, trim($horaInicio));
                $horaFin = mysqli_real_escape_string($link, trim($horaFin));

                //Validación de disponibilidad
                if (!$this->verificarDisponibilidadSala($idSalon, $fecha, $horaInicio, $horaFin)) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title fw-bold">Atención</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">La sala ya se encuentra reservada en ese horario. Por favor, elija otro bloque.</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }

                //Inserción de la reserva
                $sql = "INSERT INTO reservas (IDSalon, IDUsuario, Fecha, HoraInicio, HoraFin, CantHoras, activo) 
                        VALUES ('$idSalon', '$idUsuario', '$fecha', '$horaInicio', '$horaFin', '$cantHoras', 1)";
                
                $resultado = $objConexion->ejecutarSQL($sql);

                if ($resultado) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fw-bold">¡Reserva Exitosa!</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">¡La sala ha sido reservada con éxito para el ' . htmlspecialchars($fecha) . '!</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-success px-4 fw-bold" onclick="location.reload();">Aceptar</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                } else {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title fw-bold">Error</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">No se pudo procesar la reserva en la base de datos.</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }

            } catch (\Throwable $e) {
                return '
                <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Error de Conexión</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }
    }
    public function obtenerReservasMes($idSalon, $anio, $mes) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idSalon = mysqli_real_escape_string($link, trim($idSalon));
        $anio = (int)$anio;
        $mes = (int)$mes;

        $sql = "SELECT r.*, u.nombre as nombre_usuario FROM reservas r
                INNER JOIN usuario u ON r.IDUsuario = u.IDUsuario
                WHERE r.IDSalon = '$idSalon' 
                AND YEAR(r.Fecha) = $anio 
                AND MONTH(r.Fecha) = $mes 
                AND r.activo = 1
                ORDER BY r.HoraInicio ASC";

        $resultado = mysqli_query($link, $sql);
        $reservasPorDia = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $fecha = $row['Fecha']; // Suponiendo formato YYYY-MM-DD
                $reservasPorDia[$fecha][] = $row;
            }
        }
        return $reservasPorDia;
    }
    public function eliminarReserva($idReserva, $idUsuarioActual, $rolesUsuario) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idReservaEscaped = mysqli_real_escape_string($link, (int)$idReserva);
        $idUsuarioEscaped = mysqli_real_escape_string($link, trim($idUsuarioActual));

        $sqlCheck = "SELECT IDUsuario FROM reservas WHERE IDReservas = '$idReservaEscaped' AND activo = 1";
        $resCheck = mysqli_query($link, $sqlCheck);

        if ($resCheck && $row = mysqli_fetch_assoc($resCheck)) {
            $duenioReserva = $row['IDUsuario'];

            $esAdminOResponsable = false;
            if (is_array($rolesUsuario)) {
                foreach ($rolesUsuario as $r) {
                    $rolId = is_array($r) ? ($r['IDRol'] ?? $r['id'] ?? $r['Rol'] ?? '') : $r;
                    $rolNombre = is_array($r) ? ($r['nombre'] ?? $r['Rol'] ?? '') : (string)$r;
                    
                    if (in_array($rolId, [1, 2, '1', '2'], true) || 
                        strcasecmp(trim($rolNombre), 'Administrador') === 0 || 
                        strcasecmp(trim($rolNombre), 'DOT') === 0 || 
                        strcasecmp(trim($rolNombre), 'Responsable-CeiLab') === 0) {
                        $esAdminOResponsable = true;
                        break;
                    }
                }
            } elseif (is_numeric($rolesUsuario) || is_string($rolesUsuario)) {
                if (in_array($rolesUsuario, [1, 2, '1', '2'], true) || 
                    strcasecmp(trim($rolesUsuario), 'Administrador') === 0 || 
                    strcasecmp(trim($rolesUsuario), 'DOT') === 0 || 
                    strcasecmp(trim($rolesUsuario), 'Responsable-CeiLab') === 0) {
                    $esAdminOResponsable = true;
                }
            }

            $esDuenio = ($duenioReserva == $idUsuarioEscaped);

            if ($esAdminOResponsable || $esDuenio) {
                $sqlDelete = "UPDATE reservas SET activo = 0 WHERE IDReservas = '$idReservaEscaped'";
                if ($objConexion->ejecutarSQL($sqlDelete)) {
                    return '
                    <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title fw-bold">Reserva Cancelada</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center py-4">
                                    <p class="fs-5 text-dark mb-0">La reserva se ha eliminado correctamente.</p>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-success px-4 fw-bold" onclick="location.reload();">Entendido</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                return '
                <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold">Acceso Denegado</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">No tiene permisos para eliminar esta reserva.</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }

        return '
        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold">Atención</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 text-dark mb-0">La reserva no existe o ya fue eliminada.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                    </div>
                </div>
            </div>
        </div>';
    }
}
?>