/**
 * Interfaces relacionadas con autenticación
 * Sincronizadas con los contratos del backend UsuarioController
 */
import { User } from './user.interface';

/**
 * Credenciales para login
 */
export interface LoginRequest {
  usuario_correo: string;
  usuario_password: string;
}

/**
 * Respuesta del endpoint POST /usuarios/login
 */
export interface LoginResponse {
  usuario: User;
  token: string;
}

/**
 * Respuesta del endpoint GET /me
 */
export interface MeResponse {
  usuario: {
    usuario_id: number;
    alias: string;
    email: string;
    rol: string;
    activo: boolean;
  };
}

/**
 * Usuario autenticado (almacenado en localStorage y signals)
 */
export interface AuthUser {
  usuario_id: number;
  alias: string;
  email: string;
  rol: 'ADMIN' | 'PROJECT_MANAGER' | 'USER';
  token: string;
}
