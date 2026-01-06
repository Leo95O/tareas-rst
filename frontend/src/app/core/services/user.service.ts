import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import {
  User,
  CreateUserDto,
  UpdateUserDto,
  Role,
} from '../interfaces/user.interface';

/**
 * UserService - Servicio para gestión de usuarios (Equipo)
 * - CRUD completo de usuarios
 * - Signals para estado reactivo
 * - Auto-refresh después de operaciones
 * - Gestión de roles
 */
@Injectable({
  providedIn: 'root',
})
export class UserService {
  private readonly apiUrl = environment.apiUrl;

  // Signals
  readonly users = signal<User[]>([]);
  readonly roles = signal<Role[]>([]);
  readonly isLoading = signal<boolean>(false);

  constructor(private http: HttpClient) { }

  /**
   * GET /usuarios/admin/listar 
   * Metodo listarTodo() del backend
   */
  getUsers(): void {
    this.isLoading.set(true);

    this.http.get<ApiResponse<User[]>>(`${this.apiUrl}/usuarios/admin/listar`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.users.set(response.data);
        }
        this.isLoading.set(false);
      },
      error: () => {
        this.isLoading.set(false);
      },
    });
  }

  /**
   * POST /usuarios/admin/crear 
   * Metodo crearAdmin() del backend
   */
  createUser(userData: CreateUserDto): Observable<ApiResponse<User>> {
    return this.http.post<ApiResponse<User>>(`${this.apiUrl}/usuarios/admin/crear`, userData).pipe(
      tap(() => {
        this.getUsers();
      })
    );
  }

  /**
   * PUT /usuarios/admin/editar/{id} - 
   * Metodo editarAdmin($id) del backend
   */
  updateUser(id: number, userData: UpdateUserDto): Observable<ApiResponse<User>> {
    return this.http.put<ApiResponse<User>>(`${this.apiUrl}/usuarios/admin/editar/${id}`, userData).pipe(
      tap(() => {
        this.getUsers();
      })
    );
  }

  /**
   * DELETE /usuarios/admin/eliminar/{id} - 
   * Metodo eliminarAdmin($id) del backend
   */
  deleteUser(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/usuarios/admin/eliminar/${id}`).pipe(
      tap(() => {
        this.getUsers();
      })
    );
  }

  /**
   * PUT /usuarios/admin/editar/{id} - Activar/desactivar usuario
   */
  toggleUserStatus(id: number, activo: boolean): Observable<ApiResponse<User>> {
    // Map boolean to 1/0 for backend
    return this.updateUser(id, { usuario_estado: activo ? 1 : 0 });
  }

  /**
   * GET /roles - Obtener catálogo de roles
   */
  getRoles(): void {
    const staticRoles: Role[] = [
      { id: 1, nombre: 'ADMIN' },
      { id: 2, nombre: 'PROJECT_MANAGER' },
      { id: 3, nombre: 'USER' }
    ];
    this.roles.set(staticRoles);
  }
}
