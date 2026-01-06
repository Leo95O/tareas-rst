import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import {
  Project,
  CreateProjectDto,
  UpdateProjectDto,
  Sucursal,
  EstadoProyecto,
} from '../interfaces/project.interface';

/**
 * Servicio para gestión de proyectos
 */
@Injectable({
  providedIn: 'root',
})
export class ProjectService {
  private readonly apiUrl = environment.apiUrl;

  // Signals
  readonly projects = signal<Project[]>([]);
  readonly sucursales = signal<Sucursal[]>([]);
  readonly estadosProyecto = signal<EstadoProyecto[]>([]);
  readonly isLoading = signal<boolean>(false);

  constructor(private http: HttpClient) { }

  /**
   * GET /proyectos/listar - Obtener todos los proyectos
   */
  getProjects(): void {
    this.isLoading.set(true);

    this.http.get<ApiResponse<Project[]>>(`${this.apiUrl}/proyectos`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.projects.set(response.data);
        }
        this.isLoading.set(false);
      },
      error: () => {
        this.isLoading.set(false);
      },
    });
  }

  /**
   * GET /proyectos/listar/{id} - Obtener un proyecto por ID
   */
  getProjectById(id: number): Observable<ApiResponse<Project>> {
    return this.http.get<ApiResponse<Project>>(`${this.apiUrl}/proyectos/${id}`);
  }

  /**
   * POST /proyectos/crear - Crear un nuevo proyecto
   */
  createProject(projectData: CreateProjectDto): Observable<ApiResponse<Project>> {
    return this.http.post<ApiResponse<Project>>(`${this.apiUrl}/proyectos`, projectData).pipe(
      tap(() => {
        this.getProjects();
      })
    );
  }

  /**
   * PUT /proyectos/editar/{id} - Actualizar un proyecto existente
   */
  updateProject(id: number, projectData: UpdateProjectDto): Observable<ApiResponse<Project>> {
    return this.http.put<ApiResponse<Project>>(`${this.apiUrl}/proyectos/${id}`, projectData).pipe(
      tap(() => {
        this.getProjects();
      })
    );
  }

  /**
   * DELETE /proyectos/eliminar/{id} - Eliminar un proyecto (soft delete)
   */
  deleteProject(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/proyectos/${id}`).pipe(
      tap(() => {
        this.getProjects();
      })
    );
  }

  /**
   * GET /datamaster/sucursales - Obtener catálogo de sucursales
   */
  getSucursales(): void {
    this.http.get<ApiResponse<Sucursal[]>>(`${this.apiUrl}/datamaster/sucursales`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.sucursales.set(response.data);
        }
      },
    });
  }

  /**
   * GET /datamaster/estados-proyecto - Obtener catálogo de estados de proyecto
   */
  getEstadosProyecto(): void {
    this.http.get<ApiResponse<EstadoProyecto[]>>(`${this.apiUrl}/datamaster/estados-proyecto`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.estadosProyecto.set(response.data);
        }
      },
    });
  }
}
