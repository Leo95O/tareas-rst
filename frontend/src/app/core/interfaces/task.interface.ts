export interface Task {
  // --- Coinciden con Backend (Tarea.php::toArray) ---
  id: number;
  titulo: string;
  descripcion: string;
  fecha_limite: string;
  
  // IDs (El backend ahora envía 'asignado_id' y 'creador_id')
  proyecto_id: number;
  asignado_id: number; 
  creador_id: number;

  // Objetos Hidratados (Composición)
  estado?: {
    id: number;
    nombre: string;
  };
  prioridad?: {
    id: number;
    nombre: string;
    valor: number;
  };
  categoria?: {
    id: number;
    nombre: string;
  };

  // --- Campos Legacy / Calculados ---
  // (Marcados como opcionales porque el Backend nuevo NO los envía por defecto en toArray)
  // Debes verificar si tus vistas los usan y considerar usar los objetos de arriba o IDs.
  fecha_creacion?: string;
  es_vencida?: number;
  eliminada?: boolean;
  
  proyecto?: string; // Nombre del proyecto (antes venía plano)
  sucursal?: string; // Nombre de sucursal
  usuario_asignado_nombre?: string;
  
  // Compatibilidad inversa (si el frontend intenta leer task.estado_id directamente)
  estado_id?: number; 
  prioridad_id?: number;
  usuario_asignado_id?: number; // Alias de asignado_id
}

export interface TasksByEstado {
  estado: {
    estado_id: number;
    nombre: string;
    color: string;
    orden: number;
  };
  tasks: Task[];
}

export interface CreateTaskDto {
  titulo: string;
  descripcion: string;
  fecha_limite: string;
  proyecto_id: number;
  prioridad_id: number;
  estado_id?: number;
  usuario_asignado_id: number; // El controller suele mapear esto al crear
}

export interface UpdateTaskDto {
  titulo?: string;
  descripcion?: string;
  fecha_limite?: string;
  estado_id?: number;
  prioridad_id?: number;
  usuario_asignado_id?: number;
}