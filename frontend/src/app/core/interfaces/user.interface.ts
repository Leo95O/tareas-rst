export interface User {
  usuario_id: number;
  usuario_nombre: string;
  usuario_correo: string;
  fecha_creacion: string;
  token?: string;

  // IDs Planos (Mantenidos para formularios)
  rol_id: number;
  usuario_estado: number;

  // Objetos Hidratados (NUEVO: Para visualización directa)
  rol?: {
    id: number;
    nombre: string;
  };
  estado?: {
    id: number;
    nombre: string;
    descripcion?: string;
  };
}

export interface CreateUserDto {
  usuario_nombre: string;
  usuario_correo: string;
  usuario_password: string;
  rol_id: number;
}

export interface UpdateUserDto {
  usuario_nombre?: string;
  usuario_correo?: string;
  usuario_password?: string;
  rol_id?: number;
  usuario_estado?: number;
}

export interface Role {
  id: number;
  nombre: string;
}