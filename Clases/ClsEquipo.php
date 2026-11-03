<?php
require_once __DIR__ . '/../Persistencia/connect.php';

class ClsEquipo {

    //Método para registrar un nuevo equipo
    public function registrarEquipo($estado, $observaciones, $tipo, $idSala) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $estado = mysqli_real_escape_string($link, trim($estado));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $tipo = mysqli_real_escape_string($link, trim($tipo));
        $idSala = mysqli_real_escape_string($link, trim($idSala));

        $disponibilidad = ($estado === 'Disponible') ? 1 : 0;

        try {
            //Se incluye activo = 1 por defecto al registrar, en la base de datos también lo hace
            $sql = "INSERT INTO equipo (Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad, activo) 
                    VALUES ('$estado', '$observaciones', '$tipo', '$idSala', '$disponibilidad', 1)";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
    //Método para listar equipos activos filtrando por tipo y disponibilidad
    public function listarEquipos($filtroTipo = '', $filtroDisponibilidad = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $sql = "SELECT IDEquipo, Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad 
                FROM equipo WHERE activo = 1";

        if (!empty($filtroTipo)) {
            $tipoEscaped = mysqli_real_escape_string($link, $filtroTipo);
            $sql .= " AND Tipo = '$tipoEscaped'";
        }
        if ($filtroDisponibilidad !== '' && $filtroDisponibilidad !== null) {
            $dispEscaped = mysqli_real_escape_string($link, $filtroDisponibilidad);
            $sql .= " AND Disponibilidad = '$dispEscaped'";
        }

        $sql .= " ORDER BY IDEquipo ASC";

        $resultado = mysqli_query($link, $sql);
        
        $equipos = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $equipos[] = $row;
            }
        }
        return $equipos;
    }
    //Método para obtener equipo por su ID
    public function obtenerEquipoPorId($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, $idEquipo);
        $sql = "SELECT IDEquipo, Estado, Observaciones, Tipo, IDSala_ceilab, Disponibilidad FROM equipo WHERE IDEquipo = '$idEquipo' AND activo = 1";
        $resultado = mysqli_query($link, $sql);

        if ($resultado && $row = mysqli_fetch_assoc($resultado)) {
            return $row;
        }
        return null;
    }
    //Método para modificar un equipo
    public function modificarEquipo($idEquipo, $estado, $observaciones, $tipo, $idSala) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        $idEquipo = mysqli_real_escape_string($link, trim($idEquipo));
        $estado = mysqli_real_escape_string($link, trim($estado));
        $observaciones = mysqli_real_escape_string($link, trim($observaciones));
        $tipo = mysqli_real_escape_string($link, trim($tipo));
        $idSala = mysqli_real_escape_string($link, trim($idSala));

        $disponibilidad = ($estado === 'Disponible') ? 1 : 0;

        try {
            $sql = "UPDATE equipo SET 
                    Estado = '$estado', 
                    Observaciones = '$observaciones', 
                    Tipo = '$tipo', 
                    IDSala_ceilab = '$idSala', 
                    Disponibilidad = '$disponibilidad' 
                    WHERE IDEquipo = '$idEquipo'";
            return mysqli_query($link, $sql);
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
    //Método para realizar la baja lógica de un equipo 
    public function eliminarEquipo($idEquipo, $forzarReservas = false, $confirmado = false) {
        $idEquipo = (int)$idEquipo;
        $objConexion = new Conexion();
        $link = $objConexion->conectar();

        //1. RESTRICCIÓN: Si está prestado, no se puede eliminar
        if ($this->verificarPrestamoActivo($idEquipo)) {
            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold">❌ Acción Restringida</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">No se puede dar de baja el equipo porque actualmente se encuentra <b>prestado</b>.</p>
                            <small class="text-muted d-block mt-2">Debe registrar la devolución del equipo antes de proceder con la baja.</small>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Entendido</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        //2. VALIDA RESERVAS: Obtiene reservas activas asociadas
        $reservasActivas = $this->obtenerReservasActivas($idEquipo);

        //Si tiene reservas activas y NO se ha forzado la acción
        if (!empty($reservasActivas) && !$forzarReservas) {
            $idsReservasStr = implode(', #', $reservasActivas);
            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title fw-bold">⚠️ Equipo con Reservas Activas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">Este equipo tiene la(s) reserva(s) activa(s) asociada(s): <b>#' . $idsReservasStr . '</b>.</p>
                            <p class="text-muted small mt-2 mb-0">¿Desea dar de baja el equipo y marcar automáticamente esas reservas como inactivas?</p>
                        </div>
                        <div class="modal-footer justify-content-center gap-2">
                            <button type="button" class="btn btn-secondary px-3 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-warning px-4 fw-bold text-dark btn-forzar-baja" data-id="' . $idEquipo . '">Sí, dar de baja y desactivar reservas</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        //3. CONFIRMACIÓN GENERAL: Si no tiene reservas activas y todavía NO ha sido confirmado por el usuario
        if (empty($reservasActivas) && !$confirmado) {
            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold">❓ Confirmar Baja de Equipo</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">¿Está seguro de que desea dar de baja este equipo?</p>
                            <p class="text-muted small mt-2 mb-0">El equipo pasará al inventario inactivo.</p>
                        </div>
                        <div class="modal-footer justify-content-center gap-2">
                            <button type="button" class="btn btn-secondary px-3 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn bg-danger px-4 fw-bold btn-confirmar-baja text-white" data-id="' . $idEquipo . '">Sí, dar de baja</button>
                        </div>
                    </div>
                </div>
            </div>';
        }

        //4. LA BAJA: Ocurre si ya está confirmado o si se forzaron las reservas
        $textoExito = "El equipo ha sido dado de baja correctamente del sistema.";
        if (!empty($reservasActivas) && $forzarReservas) {
            $idsReservasStr = implode(',', $reservasActivas);
            $sqlInactivarReservas = "UPDATE reservas SET activo = 0 WHERE IDReservas IN ($idsReservasStr)";
            mysqli_query($link, $sqlInactivarReservas);
            $textoExito = "El equipo ha sido dado de baja correctamente y sus reservas asociadas fueron desactivadas.";
        }

        $sqlBaja = "UPDATE equipo SET activo = 0 WHERE IDEquipo = $idEquipo";
        
        try {
            $resultado = mysqli_query($link, $sqlBaja);
            if ($resultado && mysqli_affected_rows($link) > 0) {
                return '
                <div class="modal fade" id="modalRespuesta" data-recargar="true" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow border-0">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title fw-bold">¡Baja Exitosa!</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center py-4">
                                <p class="fs-5 text-dark mb-0">' . $textoExito . '</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-success px-4 fw-bold" data-bs-dismiss="modal">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>';
            } else {
                throw new \Exception("No se encontró el equipo en la base de datos o ya se encontraba inactivo.");
            }
        } catch (\Throwable $e) {
            return '
            <div class="modal fade" id="modalRespuesta" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow border-0">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold">Error</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="fs-5 text-dark mb-0">No se pudo procesar la baja del equipo.</p>
                            <small class="text-muted">' . htmlspecialchars($e->getMessage()) . '</small>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-danger px-4 fw-bold" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>';
        }
    }
    //Método para listar un equipo por disponibilidad en un rango horario determinado
    public function listarEquiposDisponibles($fecha, $horaInicio, $horaFin, $tipo = '') {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $fecha = mysqli_real_escape_string($link, $fecha);
        $horaInicio = mysqli_real_escape_string($link, strlen($horaInicio) == 5 ? $horaInicio . ':00' : $horaInicio);
        $horaFin = mysqli_real_escape_string($link, strlen($horaFin) == 5 ? $horaFin . ':00' : $horaFin);
        
        $filtroTipo = !empty($tipo) ? "AND e.Tipo = '" . mysqli_real_escape_string($link, $tipo) . "'" : "";

        $sql = "
            SELECT e.* 
            FROM equipo e
            WHERE e.activo = 1 
              AND e.Disponibilidad = 1
              $filtroTipo
              -- 1. Excluir equipos ocupados por reservas activas
              AND NOT EXISTS (
                  SELECT 1 
                  FROM tiene_equipos te
                  JOIN reservas r ON te.IDReservas = r.IDReservas
                  WHERE te.IDEquipo = e.IDEquipo
                    AND r.activo = 1 
                    AND r.Fecha = '$fecha'
                    AND (TIME('$horaInicio') < TIME(r.HoraFin) AND TIME('$horaFin') > TIME(r.HoraInicio))
              )
              -- 2. Excluir equipos con préstamos activos (vinculados por orden de IDOperaciones)
              AND NOT EXISTS (
                  SELECT 1 
                  FROM operaciones o_prestamo
                  WHERE o_prestamo.IDEquipo = e.IDEquipo
                    AND o_prestamo.Accion = 1 
                    AND o_prestamo.Fecha = '$fecha'
                    AND o_prestamo.HoraInicio IS NOT NULL 
                    AND o_prestamo.HoraFin IS NOT NULL
                    AND TIME('$horaInicio') < COALESCE(
                        (
                            SELECT MIN(o_dev.HoraInicio)
                            FROM operaciones o_dev
                            WHERE o_dev.IDEquipo = e.IDEquipo
                              AND o_dev.Accion = 2 
                              AND o_dev.Fecha = '$fecha'
                              AND o_dev.IDOperaciones > o_prestamo.IDOperaciones
                        ),
                        TIME(o_prestamo.HoraFin)
                    )
                    AND TIME('$horaFin') > TIME(o_prestamo.HoraInicio)
              )
            ORDER BY e.Tipo ASC, e.IDEquipo ASC;
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
    //Método para obtener los tipos de equipos para el selector
    public function obtenerTiposEquipos() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $sql = "SELECT DISTINCT Tipo FROM equipo WHERE activo = 1 AND Tipo IS NOT NULL AND Tipo != '' ORDER BY Tipo ASC";
        $resultado = mysqli_query($link, $sql);
        $tipos = [];
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $tipos[] = $row['Tipo'];
            }
        }
        return $tipos;
    }
    //Lista equipos inactivos
    public function listarEquiposInactivos() {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        
        $sql = "SELECT * FROM equipo WHERE activo = 0 ORDER BY IDEquipo DESC";
        return mysqli_query($link, $sql);
    }
    //Reactivo el equipo 
    public function reactivarEquipo($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idEquipo = (int)$idEquipo;

        //Cambia el estado a activo 
        $sql = "UPDATE equipo SET activo = 1 WHERE IDEquipo = $idEquipo";
        return mysqli_query($link, $sql);
    }
    public function verificarPrestamoActivo($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idEquipo = (int)$idEquipo;

        $sql = "
            SELECT 1 
            FROM operaciones o_prestamo
            WHERE o_prestamo.IDEquipo = $idEquipo
              AND o_prestamo.Accion = 1 
              AND NOT EXISTS (
                  SELECT 1 
                  FROM operaciones o_dev
                  WHERE o_dev.IDEquipo = $idEquipo
                    AND o_dev.Accion = 2 
                    AND o_dev.IDOperaciones > o_prestamo.IDOperaciones
              )
            LIMIT 1;
        ";
        $resultado = mysqli_query($link, $sql);
        return ($resultado && mysqli_num_rows($resultado) > 0);
    }
    public function obtenerReservasActivas($idEquipo) {
        $objConexion = new Conexion();
        $link = $objConexion->conectar();
        $idEquipo = (int)$idEquipo;

        $sql = "SELECT r.IDReservas FROM tiene_equipos te 
                JOIN reservas r ON te.IDReservas = r.IDReservas 
                WHERE te.IDEquipo = $idEquipo AND r.activo = 1";
        $resultado = mysqli_query($link, $sql);
        
        $reservas = [];
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $reservas[] = $row['IDReservas'];
            }
        }
        return $reservas;
    }
}
?>