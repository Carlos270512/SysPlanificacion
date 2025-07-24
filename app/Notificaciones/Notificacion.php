<?php

class Notificacion {
    private $id_observacion;
    private $planificacion_id;
    private $unidad_id;
    private $asignatura_codigo;
    private $docente_codigo;
    private $nombre_archivo;
    private $observacion;
    private $usuario_revisa;
    private $fecha_observacion;
    private $fecha_correccion;
    private $estado;
    
    // Datos adicionales para la notificación
    private $asignatura_nombre;
    private $unidad_nombre;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->llenarDatos($data);
        }
    }

    private function llenarDatos($data) {
        $this->id_observacion = $data['id_observacion'] ?? null;
        $this->planificacion_id = $data['planificacion_id'] ?? null;
        $this->unidad_id = $data['unidad_id'] ?? null;
        $this->asignatura_codigo = $data['asignatura_codigo'] ?? null;
        $this->docente_codigo = $data['docente_codigo'] ?? null;
        $this->nombre_archivo = $data['nombre_archivo'] ?? null;
        $this->observacion = $data['observacion'] ?? null;
        $this->usuario_revisa = $data['usuario_revisa'] ?? null;
        $this->fecha_observacion = $data['fecha_observacion'] ?? null;
        $this->fecha_correccion = $data['fecha_correccion'] ?? null;
        $this->estado = $data['estado'] ?? null;
        $this->asignatura_nombre = $data['asignatura_nombre'] ?? null;
        $this->unidad_nombre = $data['unidad_nombre'] ?? null;
    }

    // Getters
    public function getIdObservacion() { return $this->id_observacion; }
    public function getPlanificacionId() { return $this->planificacion_id; }
    public function getUnidadId() { return $this->unidad_id; }
    public function getAsignaturaCodigo() { return $this->asignatura_codigo; }
    public function getDocenteCodigo() { return $this->docente_codigo; }
    public function getNombreArchivo() { return $this->nombre_archivo; }
    public function getObservacion() { return $this->observacion; }
    public function getUsuarioRevisa() { return $this->usuario_revisa; }
    public function getFechaObservacion() { return $this->fecha_observacion; }
    public function getFechaCorreccion() { return $this->fecha_correccion; }
    public function getEstado() { return $this->estado; }
    public function getAsignaturaNombre() { return $this->asignatura_nombre; }
    public function getUnidadNombre() { return $this->unidad_nombre; }

    // Setters
    public function setIdObservacion($id_observacion) { $this->id_observacion = $id_observacion; }
    public function setPlanificacionId($planificacion_id) { $this->planificacion_id = $planificacion_id; }
    public function setUnidadId($unidad_id) { $this->unidad_id = $unidad_id; }
    public function setAsignaturaCodigo($asignatura_codigo) { $this->asignatura_codigo = $asignatura_codigo; }
    public function setDocenteCodigo($docente_codigo) { $this->docente_codigo = $docente_codigo; }
    public function setNombreArchivo($nombre_archivo) { $this->nombre_archivo = $nombre_archivo; }
    public function setObservacion($observacion) { $this->observacion = $observacion; }
    public function setUsuarioRevisa($usuario_revisa) { $this->usuario_revisa = $usuario_revisa; }
    public function setFechaObservacion($fecha_observacion) { $this->fecha_observacion = $fecha_observacion; }
    public function setFechaCorreccion($fecha_correccion) { $this->fecha_correccion = $fecha_correccion; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setAsignaturaNombre($asignatura_nombre) { $this->asignatura_nombre = $asignatura_nombre; }
    public function setUnidadNombre($unidad_nombre) { $this->unidad_nombre = $unidad_nombre; }

    // Convertir a array para JSON
    public function toArray() {
        return [
            'id_observacion' => $this->id_observacion,
            'planificacion_id' => $this->planificacion_id,
            'unidad_id' => $this->unidad_id,
            'asignatura_codigo' => $this->asignatura_codigo,
            'docente_codigo' => $this->docente_codigo,
            'nombre_archivo' => $this->nombre_archivo,
            'observacion' => $this->observacion,
            'usuario_revisa' => $this->usuario_revisa,
            'fecha_observacion' => $this->fecha_observacion,
            'fecha_correccion' => $this->fecha_correccion,
            'estado' => $this->estado,
            'asignatura' => $this->asignatura_nombre,
            'unidad' => $this->unidad_nombre
        ];
    }
}
?>