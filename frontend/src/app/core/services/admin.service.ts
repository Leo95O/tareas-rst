import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import {
  AdminDashboardResponse,
  AdminUserInfo,
} from '../interfaces/admin.interface';
import { environment } from '../../../environments/environment';

/**
 * AdminService - Gestión del dashboard y funcionalidades del administrador
 * - Consumo del endpoint GET /admin/home
 */
@Injectable({
  providedIn: 'root',
})
export class AdminService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  // Signals para estado reactivo
  readonly adminInfo = signal<AdminUserInfo | null>(null);
  readonly permisos = signal<string[]>([]);
  readonly isLoading = signal<boolean>(false);

  /**
   * Obtener información del dashboard del administrador
   * GET /admin/home
   */
  getDashboardData(): Observable<AdminDashboardResponse> {
    this.isLoading.set(true);

    return this.http.get<AdminDashboardResponse>(`${this.apiUrl}/admin/home`).pipe(
      tap((response) => {
        if (response.tipo === 1) {
          this.adminInfo.set(response.data.usuario_info);
          this.permisos.set(response.data.permisos);
        }
        this.isLoading.set(false);
      })
    );
  }

  /**
   * Limpiar estado del servicio
   */
  clearState(): void {
    this.adminInfo.set(null);
    this.permisos.set([]);
    this.isLoading.set(false);
  }
}
