import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

/**
 * AuthGuard - Protege rutas que requieren autenticación
 * 
 * Verifica que:
 * 1. Exista un token válido en localStorage
 * 2. El usuario esté autenticado
 * 
 * Si no está autenticado, redirige a /login
 */
export const authGuard = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Verificar directamente en localStorage (más confiable en carga inicial)
  const token = localStorage.getItem('auth_token');
  const userData = localStorage.getItem('user_data');

  if (token && userData) {
    // Asegurar que el signal esté actualizado
    if (!authService.currentUser()) {
      authService.initializeAuth();
    }
    return true;
  }

  // No autenticado, redirigir a login
  router.navigate(['/login']);
  return false;
};

/**
 * NoAuthGuard - Protege rutas que NO requieren autenticación (login, registro)
 * 
 * Si el usuario ya está autenticado, redirige al dashboard correspondiente
 */
export const noAuthGuard = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Verificar directamente en localStorage
  const token = localStorage.getItem('auth_token');
  const userData = localStorage.getItem('user_data');

  if (token && userData) {
    // Asegurar que el signal esté actualizado
    if (!authService.currentUser()) {
      authService.initializeAuth();
    }

    const user = JSON.parse(userData);
    // Ya está autenticado, redirigir según rol
    switch (user.rol_id) {
      case 1: // ADMIN
        router.navigate(['/admin/dashboard']);
        break;
      case 2: // PROJECT_MANAGER
        router.navigate(['/project-manager/dashboard']);
        break;
      case 3: // USER
        router.navigate(['/user/mis-tareas']);
        break;
      default:
        router.navigate(['/login']);
    }
    return false;
  }

  return true;
};

/**
 * RoleGuard - Protege rutas según el rol del usuario
 * 
 * @param allowedRoles Array de roles permitidos ('ADMIN', 'PROJECT_MANAGER', 'USER')
 */
export const roleGuard = (allowedRoles: string[]) => {
  return () => {
    const authService = inject(AuthService);
    const router = inject(Router);

    // Verificar directamente en localStorage
    const userData = localStorage.getItem('user_data');
    if (!userData) {
      router.navigate(['/login']);
      return false;
    }

    const user = JSON.parse(userData);
    const roleMap: { [key: string]: number } = {
      'ADMIN': 1,
      'PROJECT_MANAGER': 2,
      'USER': 3
    };

    const hasRole = allowedRoles.some(role => roleMap[role] === user.rol_id);

    if (hasRole) {
      return true;
    }

    // No tiene el rol necesario, redirigir a login
    router.navigate(['/login']);
    return false;
  };
};

