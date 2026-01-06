export interface Task {
  id: number;
  titulo: string;
  descripcion: string;
  fecha_limite: string;
  proyecto: string;
  proyecto_id: number;
  sucursal: string;
  estado: string;
  estado_id: number;
  prioridad: string;
  prioridad_id?: number;
  usuario_asignado_nombre: string;
  usuario_asignado_id: number;
  fecha_creacion: string;
  es_vencida?: number;
  eliminada: boolean;
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
  usuario_asignado_id: number;
}

export interface UpdateTaskDto {
  titulo?: string;
  descripcion?: string;
  fecha_limite?: string;
  estado_id?: number;
  prioridad_id?: number;
  usuario_asignado_id?: number;
}
