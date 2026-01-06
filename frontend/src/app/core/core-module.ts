import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { authInterceptor } from './interceptors/auth.interceptor';

/**
 * Módulo singleton para servicios core de la aplicación
 * - HttpClient con interceptor de autenticación
 * - AuthService (providedIn: 'root')
 */
@NgModule({
  declarations: [],
  imports: [CommonModule],
  providers: [
    provideHttpClient(
      withInterceptors([authInterceptor])
    ),
  ],
})
export class CoreModule { }
