export interface Project {
  // --- Coinciden con Backend (Proyecto.php::toArray) ---
  id: number;
  nombre: string;
  descripcion: string;
  sucursal_id: number;
  creador_id: number;
  fecha_inicio: string;
  fecha_fin: string | null;
  fecha_creacion: string;

  // ID de estado (siempre presente)
  estado_id: number;

  // Objeto Hidratado (NUEVO)
  estado?: {
    id: number;
    nombre: string;
    orden: number;
  };

  // --- Campos Legacy / Opcionales ---
  // Estos campos no vienen directamente en la entidad base del backend actual.
  // Se mantienen opcionales para no romper vistas antiguas que los esperen.
  sucursal_nombre?: string;
  estado_nombre?: string;
  creador_nombre?: string;
  eliminado?: boolean;
}

export interface CreateProjectDto {
  nombre: string;
  descripcion?: string;
  sucursal_id: number;
  estado_id?: number;
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface UpdateProjectDto {
  nombre?: string;
  descripcion?: string;
  sucursal_id?: number;
  estado_id?: number;
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface Sucursal {
  id: number;
  nombre: string;
  direccion?: string;
}

export interface EstadoProyecto {
  id: number;
  nombre: string;
  orden?: number; 
}