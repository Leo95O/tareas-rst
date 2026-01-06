/**
 * Interfaces para el módulo de administración
 * Sincronizadas con las respuestas del backend UsuarioController
 */

/**
 * Respuesta del endpoint GET /admin/home
 */
export interface AdminDashboardResponse {
  tipo: number;
  mensajes: string[];
  data: {
    usuario_info: AdminUserInfo;
    permisos: string[];
    mensaje_bienvenida: string;
  };
}

/**
 * Información del usuario admin
 */
export interface AdminUserInfo {
  id: number;
  nombre: string;
  email: string;
  rol: string;
}

/**
 * Item del menú del drawer
 */
export interface MenuItem {
  label: string;
  icon: string;
  route?: string;
  command?: () => void;
  items?: MenuItem[];
}
