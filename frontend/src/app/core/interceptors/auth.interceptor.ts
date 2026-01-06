import { HttpInterceptorFn, HttpRequest, HttpHandlerFn, HttpEvent, HttpErrorResponse, HttpResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';
import { MessageService } from 'primeng/api';
import { Router } from '@angular/router';
import { ApiResponse } from '../interfaces/api-response.interface';

export const authInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next: HttpHandlerFn): Observable<HttpEvent<unknown>> => {
  const authService = inject(AuthService);
  const messageService = inject(MessageService);
  const router = inject(Router);
  const token = authService.getToken();

  // No agregar token en rutas públicas
  const isPublicRoute = req.url.includes('/login') || req.url.includes('/registro');

  let authReq = req;
  if (token && !isPublicRoute) {
    authReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
  }

  return next(authReq).pipe(
    map((event: HttpEvent<any>) => {
      if (event instanceof HttpResponse) {
        const body = event.body as ApiResponse<any>;

        // Handle API Wrapper Logic
        if (body && typeof body.tipo === 'number') {
          if (body.tipo !== 1) {
            // Error or Warning
            const severity = body.tipo === 2 ? 'warn' : 'error';
            const summary = body.tipo === 2 ? 'Alerta' : 'Error';

            if (body.mensajes && body.mensajes.length > 0) {
              body.mensajes.forEach(msg => {
                messageService.add({ severity, summary, detail: msg });
              });
            }

            if (body.tipo === 3) {
              throw new Error(body.mensajes ? body.mensajes.join(', ') : 'Unknown Error');
            }
          }
        }
      }
      return event;
    }),
    catchError((error: HttpErrorResponse) => {
      let errorMessage = 'Ocurrió un error inesperado';

      if (error.status === 401) {
        // No hacer logout si el error viene de rutas públicas (login/registro)
        const isPublicRoute = error.url?.includes('/login') || error.url?.includes('/registro');

        if (!isPublicRoute) {
          // Token inválido o alterado - cerrar sesión automáticamente
          errorMessage = 'Sesión inválida. Por favor inicia sesión nuevamente.';

          // Mostrar mensaje antes de redirigir
          messageService.add({
            severity: 'error',
            summary: 'Sesión Expirada',
            detail: errorMessage,
            life: 3000
          });

          // Cerrar sesión y redirigir a login
          setTimeout(() => {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
            authService.currentUser.set(null);
            router.navigate(['/login']);
          }, 500);

          return throwError(() => error);
        } else {
          // Para rutas públicas, solo mostrar el error del backend
          errorMessage = error.error?.mensajes?.join(', ') || 'Credenciales inválidas';
        }
      } else if (error.status === 403) {
        // Token válido pero sin permisos - cerrar sesión
        errorMessage = 'No tienes permisos para realizar esta acción.';

        messageService.add({
          severity: 'error',
          summary: 'Acceso Denegado',
          detail: errorMessage,
          life: 3000
        });

        // Redirigir a login
        setTimeout(() => {
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user_data');
          authService.currentUser.set(null);
          router.navigate(['/login']);
        }, 500);

        return throwError(() => error);
      } else if (error.error && error.error.mensajes) {
        errorMessage = error.error.mensajes.join(', ');
      }

      messageService.add({ severity: 'error', summary: 'Error', detail: errorMessage });
      return throwError(() => error);
    })
  );
};
