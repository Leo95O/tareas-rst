import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import {
  Task,
  TasksByEstado,
  CreateTaskDto,
  UpdateTaskDto,
} from '../interfaces/task.interface';

/**
 * TaskService - Servicio para gestión de tareas
 * Consume endpoints de TaskController
 */
@Injectable({
  providedIn: 'root',
})
export class TaskService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  // Signals
  readonly tasks = signal<Task[]>([]);
  readonly tasksByEstado = signal<TasksByEstado[]>([]);
  readonly isLoading = signal<boolean>(false);
  readonly errorMessage = signal<string>('');

  // Signals para catálogos
  readonly estados = signal<any[]>([]);
  readonly prioridades = signal<any[]>([]);

  // Signal para Bolsa de Tareas
  readonly taskPool = signal<Task[]>([]);

  /**
   * Obtener todas las tareas (con filtro opcional por proyecto)
   */
  getTasks(projectId?: number): void {
    this.isLoading.set(true);
    this.errorMessage.set('');

    const url = projectId ? `${this.apiUrl}/tareas?project_id=${projectId}` : `${this.apiUrl}/tareas`;

    this.http.get<ApiResponse<Task[]>>(url).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.tasks.set(response.data);
          this.groupTasksByEstado(response.data);
        } else {
          this.errorMessage.set(response.mensajes?.[0] || 'Error al cargar tareas');
        }
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.error?.mensajes?.[0] || 'Error de conexión al cargar tareas');
        this.isLoading.set(false);
      },
    });
  }

  /**
   * Agrupar tareas por estado
   */
  private groupTasksByEstado(tasks: Task[]): void {
    // Primero, crear columnas para TODOS los estados disponibles
    const estadosDisponibles = this.estados();
    const estadosMap = new Map<number, TasksByEstado>();

    // Inicializar todas las columnas vacías
    estadosDisponibles.forEach((estado) => {
      estadosMap.set(estado.id, {
        estado: {
          estado_id: estado.id,
          nombre: estado.nombre,
          color: estado.color || '#6B7280',
          orden: estado.orden || estado.id,
        },
        tasks: [],
      });
    });

    // Luego, agregar las tareas a sus respectivas columnas
    tasks.forEach((task) => {
      const estadoId = (task as any).estado_id; // Usar estado_id directo del backend

      if (estadosMap.has(estadoId)) {
        estadosMap.get(estadoId)!.tasks.push(task);
      }
    });

    // Convertir Map a array y ordenar por estado.orden
    const grouped = Array.from(estadosMap.values()).sort(
      (a, b) => a.estado.orden - b.estado.orden
    );

    this.tasksByEstado.set(grouped);
  }

  /**
   * Cargar estados de tarea
   */
  getEstados(): void {
    this.http.get<ApiResponse<any[]>>(`${this.apiUrl}/datamaster/estados`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.estados.set(response.data);
        }
      },
    });
  }

  /**
   * Cargar prioridades
   */
  getPrioridades(): void {
    this.http.get<ApiResponse<any[]>>(`${this.apiUrl}/datamaster/prioridades`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.prioridades.set(response.data);
        }
      },
    });
  }



  /**
   * Crear nueva tarea
   */
  createTask(taskData: CreateTaskDto): Observable<ApiResponse<Task>> {
    return this.http.post<ApiResponse<Task>>(`${this.apiUrl}/tareas`, taskData);
  }

  /**
   * Actualizar tarea existente
   */
  updateTask(taskId: number, taskData: UpdateTaskDto): Observable<ApiResponse<Task>> {
    return this.http.put<ApiResponse<Task>>(`${this.apiUrl}/tareas/${taskId}`, taskData);
  }

  /**
   * Eliminar tarea (soft delete)
   */
  deleteTask(taskId: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/tareas/${taskId}`);
  }

  /**
   * Limpiar estado del servicio
   */
  clearState(): void {
    this.tasks.set([]);
    this.tasksByEstado.set([]);
    this.taskPool.set([]);
    this.isLoading.set(false);
    this.errorMessage.set('');
  }

  /**
   * Obtener tareas sin asignar 
   */
  getTaskPool(): void {
    this.isLoading.set(true);
    this.errorMessage.set('');

    this.http.get<ApiResponse<Task[]>>(`${this.apiUrl}/tareas/bolsa`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.taskPool.set(response.data);
        } else {
          this.errorMessage.set(response.mensajes?.[0] || 'Error al cargar tareas disponibles');
        }
        this.isLoading.set(false);
      },
      error: (error) => {
        this.errorMessage.set(error.error?.mensajes?.[0] || 'Error de conexión');
        this.isLoading.set(false);
      },
    });
  }

  /**
   * Auto-asignarse una tarea 
   */
  assignTaskToMe(taskId: number): Observable<ApiResponse<any>> {
    return this.http.put<ApiResponse<any>>(`${this.apiUrl}/tareas/${taskId}/asignarme`, {});
  }
}
