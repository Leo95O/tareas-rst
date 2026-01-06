export interface User {
  usuario_id: number;
  usuario_nombre: string;
  usuario_correo: string;
  rol_id: number;
  usuario_estado: number;
  fecha_creacion: string;
  token?: string;
}

export interface CreateUserDto {
  usuario_nombre: string;
  usuario_correo: string;
  password: string;
  rol_id: number;
}

export interface UpdateUserDto {
  usuario_nombre?: string;
  usuario_correo?: string;
  password?: string;
  rol_id?: number;
  usuario_estado?: number;
}

export interface Role {
  id: number;
  nombre: string;
}
