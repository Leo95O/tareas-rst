import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import { User } from '../interfaces/user.interface';
import { LoginResponse } from '../interfaces/auth.interface';
import { DataMasterService } from './data-master.service'; // <--- 1. Importar

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly router = inject(Router);
  private readonly dataMaster = inject(DataMasterService); // <--- 2. Inyectar
  
  private readonly apiUrl = environment.apiUrl;
  private readonly tokenKey = 'auth_token';
  private readonly userKey = 'user_data';

  readonly currentUser = signal<User | null>(null);

  constructor() {
    this.loadUserFromStorage();
  }

  // --- Login ---
  login(credentials: { email: string; password: string }): Observable<ApiResponse<LoginResponse>> {
    const payload = {
      usuario_correo: credentials.email,
      usuario_password: credentials.password
    };
    
    // Nota: Asegúrate que en tu backend /usuarios/login esté FUERA del middleware de Auth
    return this.http.post<ApiResponse<LoginResponse>>(`${this.apiUrl}/usuarios/login`, payload)
      .pipe(
        tap(response => {
          if (response.tipo === 1 && response.data.token) {
            this.setSession(response.data);
            // Opcional: Precargar catálogos al hacer login
            this.dataMaster.loadAll().subscribe(); 
          }
        })
      );
  }

  // --- Registro ---
  register(data: { nombre: string; email: string; password: string }): Observable<ApiResponse<any>> {
    return this.http.post<ApiResponse<any>>(`${this.apiUrl}/usuarios/registro`, data);
  }

  // --- Logout ---
  logout() {
    // 1. Limpiar Storage
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.userKey);
    
    // 2. Limpiar Estados en Memoria
    this.currentUser.set(null);
    this.dataMaster.clear(); // <--- 3. Limpiar Catálogos Globales
    
    // 3. Redirigir
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  initializeAuth() {
    this.loadUserFromStorage();
  }

  private setSession(data: LoginResponse) {
    localStorage.setItem(this.tokenKey, data.token);

    // Guardamos el usuario completo (que ahora puede incluir rol y estado como objetos)
    const user = { ...data.usuario, token: data.token };
    localStorage.setItem(this.userKey, JSON.stringify(user));
    this.currentUser.set(user);
  }

  hasRole(allowedRoles: string[]): boolean {
    const user = this.currentUser();
    if (!user) return false;

    // Mapa de Roles vs IDs (Debe coincidir con App\Constants\Roles del Backend)
    const roleMap: { [key: string]: number } = {
      'ADMIN': 1,
      'PROJECT_MANAGER': 2,
      'USER': 3
    };

    // Usamos rol_id que siempre viene en la interfaz User (plano)
    const userRoleId = user.rol_id;
    return allowedRoles.some(role => roleMap[role] === userRoleId);
  }

  private loadUserFromStorage() {
    const userData = localStorage.getItem(this.userKey);
    if (userData) {
      try {
        this.currentUser.set(JSON.parse(userData));
      } catch (e) {
        console.error('Error parsing user data', e);
        this.logout();
      }
    }
  }
}