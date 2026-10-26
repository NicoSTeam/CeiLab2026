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
    public function registrarReservaEquipos($idUsuario, $fecha, $horaInicio, $horaFin, $arrayEquipos) {
        if (empty($arrayEquipos) || !is_array($arrayEquipos)) {
            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title fw-bold">Atención</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">Debe seleccionar al menos un equipo para realizar la reserva.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idUsuarioEscaped = mysqli_real_escape_string($link, trim($idUsuario));
        $fechaEscaped = mysqli_real_escape_string($link, trim($fecha));
        $inicioEscaped = mysqli_real_escape_string($link, trim($horaInicio));
        $finEscaped = mysqli_real_escape_string($link, trim($horaFin));

        // Obtener el IDSala_ceilab del primer equipo seleccionado para cumplir con el NOT NULL de la BD
        $primerEquipo = mysqli_real_escape_string($link, trim($arrayEquipos[0]));
        $sqlSalonEq = "SELECT IDSala_ceilab FROM equipo WHERE IDEquipo = '$primerEquipo' LIMIT 1";
        $resSalonEq = mysqli_query($link, $sqlSalonEq);
        $idSalon = 1; // Valor por defecto de respaldo
        if ($resSalonEq && $rowSalon = mysqli_fetch_assoc($resSalonEq)) {
            $idSalon = $rowSalon['IDSala_ceilab'] ?? 1;
        }

        // Calcular cantidad de horas
        $timestampInicio = strtotime($horaInicio);
        $timestampFin = strtotime($horaFin);
        $canthoras = ($timestampFin - $timestampInicio) / 3600;

        //Inserto la cabecera en la tabla reservas usando la misma conexión ($link)
        $sqlReserva = "INSERT INTO reservas (CantHoras, IDSalon, IDUsuario, Fecha, HoraInicio, HoraFin, activo) 
                       VALUES ('$canthoras', '$idSalon', '$idUsuarioEscaped', '$fechaEscaped', '$inicioEscaped', '$finEscaped', 1)";

        if (mysqli_query($link, $sqlReserva)) {
            //Tomamos el ID generado en esta misma conexión
            $idReservaGenerado = mysqli_insert_id($link);

            //Inserto cada equipo seleccionado en la tabla tiene_equipos con el ID real
            foreach ($arrayEquipos as $idEquipo) {
                $idEquipoEscaped = mysqli_real_escape_string($link, trim($idEquipo));
                $sqlTieneEquipo = "INSERT INTO tiene_equipos (IDEquipo, IDReservas) VALUES ('$idEquipoEscaped', '$idReservaGenerado')";
                mysqli_query($link, $sqlTieneEquipo);
            }

            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fw-bold">¡Reserva Exitosa!</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">La reserva de los equipos seleccionados se ha registrado correctamente.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-success px-4 fw-bold" onclick="location.reload();">Entendido</button>
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
                            <p class="fs-5 text-dark mb-0">Hubo un error al procesar la reserva de equipos.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>';
        }
    }
    public function obtenerReservasEquiposMes($anio, $mes) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $anio = (int)$anio;
        $mes = (int)$mes;

        $sql = "
            SELECT r.IDReservas, r.Fecha, r.HoraInicio, r.HoraFin, r.IDUsuario, 
                   u.Nombre AS nombre_usuario, te.IDEquipo, e.Tipo
            FROM reservas r
            JOIN usuario u ON r.IDUsuario = u.IDUsuario
            JOIN tiene_equipos te ON r.IDReservas = te.IDReservas
            JOIN equipo e ON te.IDEquipo = e.IDEquipo
            WHERE r.activo = 1 
              AND YEAR(r.Fecha) = $anio 
              AND MONTH(r.Fecha) = $mes
            ORDER BY r.Fecha ASC, r.HoraInicio ASC;
        ";

        $resultado = mysqli_query($link, $sql);
        $reservasPorDia = [];

        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $fecha = $row['Fecha'];
                $reservasPorDia[$fecha][] = $row;
            }
        }

        return $reservasPorDia;
    }
    public function eliminarEquipoDeReserva($idReserva, $idEquipo, $idUsuarioSesion, $rolesSesion) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idReserva = (int)$idReserva;
        $idEquipo = mysqli_real_escape_string($link, trim($idEquipo));

        //Eliminar únicamente el equipo de la tabla tiene_equipos
        $sqlDelete = "DELETE FROM tiene_equipos WHERE IDReservas = $idReserva AND IDEquipo = '$idEquipo'";
        if (mysqli_query($link, $sqlDelete)) {
            
            //Check por si quedan equipos en esta reserva
            $sqlCheck = "SELECT COUNT(*) as total FROM tiene_equipos WHERE IDReservas = $idReserva";
            $resCheck = mysqli_query($link, $sqlCheck);
            $rowCheck = mysqli_fetch_assoc($resCheck);

            // Si ya no quedan equipos asociados, elimino la cabecera de la reserva
            if ($rowCheck['total'] == 0) {
                $sqlDelReserva = "UPDATE reservas SET activo = 0 WHERE IDReservas = $idReserva";
                mysqli_query($link, $sqlDelReserva);
            }

            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fw-bold">Equipo Retirado</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">El equipo fue removido de la reserva correctamente.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-success px-4 fw-bold" onclick="location.reload();">Entendido</button>
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
                            <p class="fs-5 text-dark mb-0">No se pudo retirar el equipo de la reserva.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>';
        }
    }
    public function agregarEquiposAReserva($idReserva, $arrayEquipos) {
        if (empty($arrayEquipos) || !is_array($arrayEquipos)) {
            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title fw-bold">Atención</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">Debe seleccionar al menos un equipo para agregar.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-warning px-4 fw-bold text-dark" data-bs-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idReserva = (int)$idReserva;

        $agregados = 0;
        foreach ($arrayEquipos as $idEquipo) {
            $idEquipoEscaped = mysqli_real_escape_string($link, trim($idEquipo));
            
            // Verifico que no esté ya agregado para evitar duplicar
            $sqlCheck = "SELECT 1 FROM tiene_equipos WHERE IDReservas = $idReserva AND IDEquipo = '$idEquipoEscaped'";
            $resCheck = mysqli_query($link, $sqlCheck);
            
            if (mysqli_num_rows($resCheck) == 0) {
                $sqlIns = "INSERT INTO tiene_equipos (IDEquipo, IDReservas) VALUES ('$idEquipoEscaped', $idReserva)";
                if (mysqli_query($link, $sqlIns)) {
                    $agregados++;
                }
            }
        }

        return '
        <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold">¡Equipos Agregados!</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="fs-5 text-dark mb-0">Se han agregado ' . $agregados . ' equipo(s) correctamente a la reserva.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-success px-4 fw-bold" onclick="location.reload();">Entendido</button>
                    </div>
                </div>
            </div>
        </div>';
    }
    //Método para listar equipos disponibles para una reserva ya existente por horario (incluyendo validación de préstamos activos en operaciones)
    public function listarEquiposDisponiblesParaReservaExistente($idReserva) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idReserva = (int)$idReserva;

        //Tomo la fecha y horario de la reserva existente
        $sqlRes = "SELECT Fecha, HoraInicio, HoraFin FROM reservas WHERE IDReservas = $idReserva LIMIT 1";
        $resRes = mysqli_query($link, $sqlRes);
        if (!$resRes || mysqli_num_rows($resRes) === 0) return [];
        $datosRes = mysqli_fetch_assoc($resRes);

        $fecha = $datosRes['Fecha'];
        $horaInicio = $datosRes['HoraInicio'];
        $horaFin = $datosRes['HoraFin'];

        // Busco equipos activos, libres, que no estén asignados a esta reserva, ni ocupados por otras reservas, ni prestados en ese horario
        $sql = "
            SELECT e.IDEquipo, e.Estado, e.Observaciones, e.Tipo, e.IDSala_ceilab, e.Disponibilidad 
            FROM equipo e
            WHERE e.activo = 1 
              AND e.Disponibilidad = 1
              AND e.IDEquipo NOT IN (
                  SELECT IDEquipo FROM tiene_equipos WHERE IDReservas = $idReserva
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM tiene_equipos te
                  JOIN reservas r ON te.IDReservas = r.IDReservas
                  WHERE te.IDEquipo = e.IDEquipo
                    AND r.Fecha = '$fecha'
                    AND r.activo = 1
                    AND ('$horaInicio' < r.HoraFin AND '$horaFin' > r.HoraInicio)
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM operaciones o
                  WHERE o.IDEquipo = e.IDEquipo
                    AND o.Accion = 1
                    AND o.Fecha = '$fecha'
                    AND o.HoraInicio IS NOT NULL 
                    AND o.HoraFin IS NOT NULL
                    AND ('$horaInicio' < o.HoraFin AND '$horaFin' > o.HoraInicio)
              )
            ORDER BY e.Tipo, e.IDEquipo ASC;
        ";

        $resultado = mysqli_query($link, $sql);
        $equipos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $equipos[] = $row;
            }
        }
        return $equipos;
    }
}
?>
