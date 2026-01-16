import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import {
  Project,
  CreateProjectDto,
  UpdateProjectDto,
  Sucursal,
} from '../interfaces/project.interface';
import { DataMasterService } from './data-master.service'; // <--- 1. Importar DataMaster

/**
 * Servicio para gestión de proyectos
 * Actualizado a Rutas "Plantilla Dorada" y DataMaster
 */
@Injectable({
  providedIn: 'root',
})
export class ProjectService {
  private readonly http = inject(HttpClient);
  private readonly dataMaster = inject(DataMasterService); // <--- 2. Inyectar
  private readonly apiUrl = environment.apiUrl;

  // Signals de Datos
  readonly projects = signal<Project[]>([]);
  readonly sucursales = signal<Sucursal[]>([]); // Lista de sucursales reales (para dropdowns)
  readonly isLoading = signal<boolean>(false);

  // Getter para obtener estados desde el servicio centralizado
  get estadosProyecto() {
    return this.dataMaster.estadosProyecto;
  }

  constructor() { }

  /**
   * GET /proyectos/listar - Obtener todos los proyectos
   */
  getProjects(): void {
    this.isLoading.set(true);

    // Aseguramos carga de catálogos (estados) por si no están
    this.dataMaster.loadAll().subscribe();

    this.http.get<ApiResponse<Project[]>>(`${this.apiUrl}/proyectos/listar`).subscribe({
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
   * (Nota: Verifica si tu backend usa /proyectos/ver/{id} o solo /{id})
   */
  getProjectById(id: number): Observable<ApiResponse<Project>> {
    // Asumimos ruta estándar REST para ver detalle, o ajusta si es /proyectos/ver/:id
    return this.http.get<ApiResponse<Project>>(`${this.apiUrl}/proyectos/${id}`);
  }

  /**
   * POST /proyectos/crear
   */
  createProject(projectData: CreateProjectDto): Observable<ApiResponse<Project>> {
    return this.http.post<ApiResponse<Project>>(`${this.apiUrl}/proyectos/crear`, projectData).pipe(
      tap(() => {
        this.getProjects(); // Recargar lista
      })
    );
  }

  /**
   * PUT /proyectos/editar/{id}
   */
  updateProject(id: number, projectData: UpdateProjectDto): Observable<ApiResponse<Project>> {
    return this.http.put<ApiResponse<Project>>(`${this.apiUrl}/proyectos/editar/${id}`, projectData).pipe(
      tap(() => {
        this.getProjects();
      })
    );
  }

  /**
   * DELETE /proyectos/{id}
   */
  deleteProject(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/proyectos/${id}`).pipe(
      tap(() => {
        this.getProjects();
      })
    );
  }

  /**
   * GET /sucursales/listar
   * Obtiene la lista REAL de sucursales para el dropdown de creación de proyectos.
   * NO usa DataMaster porque DataMaster solo trae 'estados_sucursal'.
   */
  getSucursales(): void {
    this.http.get<ApiResponse<Sucursal[]>>(`${this.apiUrl}/sucursales/listar`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.sucursales.set(response.data);
        }
      },
    });
  }
}