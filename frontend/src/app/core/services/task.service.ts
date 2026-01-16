import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import {
  Task,
  TasksByEstado,
  CreateTaskDto,
  UpdateTaskDto,
} from '../interfaces/task.interface';
import { DataMasterService } from './data-master.service';

/**
 * TaskService - Servicio para gestión de tareas
 * Refactorizado para usar DataMasterService y arquitectura Plantilla Dorada
 */
@Injectable({
  providedIn: 'root',
})
export class TaskService {
  private readonly http = inject(HttpClient);
  private readonly dataMaster = inject(DataMasterService);
  private readonly apiUrl = environment.apiUrl;

  // Signals Principales
  readonly tasks = signal<Task[]>([]);
  readonly tasksByEstado = signal<TasksByEstado[]>([]); // Kanban Board
  readonly isLoading = signal<boolean>(false);
  readonly errorMessage = signal<string>('');

  // Signal para Bolsa de Tareas
  readonly taskPool = signal<Task[]>([]);

  /**
   * Obtener todas las tareas (con filtro opcional por proyecto).
   * Asegura que los catálogos estén cargados antes de procesar.
   */
  getTasks(projectId?: number): void {
    this.isLoading.set(true);
    this.errorMessage.set('');

    // 1. Aseguramos tener los estados para poder pintar las columnas vacías
    this.dataMaster.loadAll().subscribe({
      next: () => {
        // 2. Una vez seguros, pedimos las tareas
        this._fetchTasks(projectId);
      },
      error: () => {
        this.errorMessage.set('Error al cargar datos maestros del sistema.');
        this.isLoading.set(false);
      }
    });
  }

  /**
   * Método privado para la petición real de tareas
   */
  private _fetchTasks(projectId?: number): void {
    const url = projectId 
      ? `${this.apiUrl}/tareas?project_id=${projectId}` 
      : `${this.apiUrl}/tareas`;

    this.http.get<ApiResponse<Task[]>>(url).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.tasks.set(response.data);
          // Agrupamos usando los datos maestros ya cargados
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
   * Agrupar tareas por estado (Lógica de Kanban)
   */
  private groupTasksByEstado(tasks: Task[]): void {
    // Obtenemos los estados desde el servicio centralizado
    const estadosDisponibles = this.dataMaster.estadosTarea();
    
    const estadosMap = new Map<number, TasksByEstado>();

    // 1. Inicializar TODAS las columnas (incluso las vacías)
    estadosDisponibles.forEach((estado) => {
      estadosMap.set(estado.id, {
        estado: {
          estado_id: estado.id,
          nombre: estado.nombre,
          // Si el backend no envía color, ponemos uno por defecto
          color: estado.color || '#6B7280', 
          orden: estado.orden || estado.id, // Fallback al ID si no hay orden
        },
        tasks: [],
      });
    });

    // 2. Repartir las tareas en sus columnas
    tasks.forEach((task) => {
      // CORRECCIÓN:
      // Priorizamos el ID del objeto 'estado'. Usamos '??' para fallback a 'estado_id'.
      const estadoId = task.estado?.id ?? task.estado_id; 

      // Validamos que sea un número (no undefined) antes de usarlo como key
      if (estadoId !== undefined && estadosMap.has(estadoId)) {
        estadosMap.get(estadoId)!.tasks.push(task);
      }
    });

    // 3. Convertir a Array y ordenar visualmente
    const grouped = Array.from(estadosMap.values()).sort(
      (a, b) => a.estado.orden - b.estado.orden
    );

    this.tasksByEstado.set(grouped);
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
   * Obtener tareas sin asignar (Bolsa)
   */
  getTaskPool(): void {
    this.isLoading.set(true);
    this.errorMessage.set('');

    this.http.get<ApiResponse<Task[]>>(`${this.apiUrl}/tareas/bolsa`).subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.taskPool.set(response.data);
        } else {
          this.errorMessage.set(response.mensajes?.[0] || 'Error al cargar bolsa de tareas');
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