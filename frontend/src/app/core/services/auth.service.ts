import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import { User } from '../interfaces/user.interface';
import { LoginResponse } from '../interfaces/auth.interface';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private tokenKey = 'auth_token';
  currentUser = signal<User | null>(null);

  constructor(private http: HttpClient, private router: Router) {
    this.loadUserFromStorage();
  }

  // Metodo login() del backend
  login(credentials: { email: string; password: string }): Observable<ApiResponse<LoginResponse>> {
    const payload = {
      usuario_correo: credentials.email,
      usuario_password: credentials.password
    };
    return this.http.post<ApiResponse<LoginResponse>>(`${this.apiUrl}/usuarios/login`, payload)
      .pipe(
        tap(response => {
          if (response.tipo === 1 && response.data.token) {
            this.setSession(response.data);
          }
        })
      );
  }
  // Metodo registrar() del backend
  register(data: { nombre: string; email: string; password: string }): Observable<ApiResponse<any>> {
    return this.http.post<ApiResponse<any>>(`${this.apiUrl}/usuarios/registro`, data);
  }

  logout() {
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem('user_data');
    this.currentUser.set(null);
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

    const user = { ...data.usuario, token: data.token };
    localStorage.setItem('user_data', JSON.stringify(user));
    this.currentUser.set(user);
  }

  hasRole(allowedRoles: string[]): boolean {
    const user = this.currentUser();
    if (!user) return false;

    const roleMap: { [key: string]: number } = {
      'ADMIN': 1,
      'PROJECT_MANAGER': 2,
      'USER': 3
    };

    const userRoleId = user.rol_id;
    return allowedRoles.some(role => roleMap[role] === userRoleId);
  }

  private loadUserFromStorage() {
    const userData = localStorage.getItem('user_data');
    if (userData) {
      this.currentUser.set(JSON.parse(userData));
    }
  }
}
