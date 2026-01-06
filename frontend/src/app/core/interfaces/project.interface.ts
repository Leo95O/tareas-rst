export interface Project {
  id: number;
  nombre: string;
  descripcion: string;
  sucursal_id: number;
  sucursal_nombre: string;
  estado_id: number;
  estado_nombre: string;
  creador_id: number;
  creador_nombre: string;
  fecha_inicio: string;
  fecha_fin: string | null;
  fecha_creacion: string;
  eliminado: boolean;
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
}
