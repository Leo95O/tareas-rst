import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { ConfirmationService, MessageService } from 'primeng/api';
import { AuthService } from '../../core/services/auth.service';
import { TaskService } from '../../core/services/task.service';
import { UserService } from '../../core/services/user.service';
import { ProjectService } from '../../core/services/project.service';
import { Task } from '../../core/interfaces/task.interface';

/*
 * MyTasksComponent - Vista de tareas asignadas al usuario actual
*/
@Component({
  standalone: false,
  selector: 'app-my-tasks',
  templateUrl: './my-tasks.component.html',
  styleUrl: './my-tasks.component.scss',
})
export class MyTasksComponent implements OnInit {
  private readonly authService = inject(AuthService);
  private readonly taskService = inject(TaskService);
  private readonly userService = inject(UserService);
  private readonly projectService = inject(ProjectService);
  private readonly messageService = inject(MessageService);
  private readonly confirmationService = inject(ConfirmationService);

  // Signals desde los servicios
  readonly currentUser = this.authService.currentUser;
  readonly tasksLoading = this.taskService.isLoading;

  // Filtrar solo tareas del usuario actual y agrupar por estado
  readonly myTasksByEstado = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    if (!userId) return [];

    const allTasks = this.taskService.tasks();
    const estados = this.taskService.estados();

    if (!estados.length) return [];

    // Filtrar solo tareas del usuario actual
    const myTasks = allTasks.filter(task => task.usuario_asignado_id === userId);

    // Crear columnas para cada estado
    const estadosMap = new Map<number, { estado: { estado_id: number; nombre: string; color: string; orden: number }; tasks: Task[] }>();

    estados.forEach((estado) => {
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

    // Agregar tareas a sus columnas
    myTasks.forEach((task) => {
      const estadoId = (task as any).estado_id;
      if (estadosMap.has(estadoId)) {
        estadosMap.get(estadoId)!.tasks.push(task);
      }
    });

    // Convertir a array, ordenar, y filtrar columnas vacías
    return Array.from(estadosMap.values())
      .sort((a, b) => a.estado.orden - b.estado.orden)
      .filter(column => column.tasks.length > 0);
  });

  // Estadísticas personales
  readonly totalMyTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    if (!userId) return 0;

    return this.taskService.tasks().filter(
      task => task.usuario_asignado_id === userId
    ).length;
  });

  readonly completedTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    if (!userId) return 0;

    return this.taskService.tasks().filter(task => {
      const estadoNombre = (task as any).estado || '';
      return task.usuario_asignado_id === userId &&
        estadoNombre.toLowerCase() === 'completada';
    }).length;
  });

  readonly pendingTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    if (!userId) return 0;

    const estadosFinalizados = ['completada', 'cancelada'];
    return this.taskService.tasks().filter(task => {
      const estadoNombre = (task as any).estado || '';
      return task.usuario_asignado_id === userId &&
        !estadosFinalizados.includes(estadoNombre.toLowerCase());
    }).length;
  });

  readonly overdueTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    if (!userId) return 0;

    // Usar campo es_vencida calculado por el backend
    return this.taskService.tasks().filter(
      task => task.usuario_asignado_id === userId && task.es_vencida === 1
    ).length;
  });

  // Modal state
  readonly modalVisible = signal<boolean>(false);
  readonly selectedTask = signal<Task | null>(null);

  // Modal de detalle
  readonly detailModalVisible = signal<boolean>(false);
  readonly selectedTaskForDetail = signal<Task | null>(null);

  // Catálogos
  readonly proyectos = this.projectService.projects;
  readonly estados = this.taskService.estados;
  readonly prioridades = this.taskService.prioridades;
  readonly usuarios = this.userService.users;

  ngOnInit(): void {
    this.loadData();
  }

  /**
   * Cargar datos iniciales
   */
  private loadData(): void {
    this.taskService.getTasks();
    this.projectService.getProjects();
    this.taskService.getEstados();
    this.taskService.getPrioridades();

    // Solo cargar usuarios si es Admin o PM (no para usuarios normales)
    const userRolId = this.currentUser()?.rol_id;
    if (userRolId === 1 || userRolId === 2) {
      this.userService.getUsers();
    }
  }

  /**
   * Abrir modal para crear tarea
   */
  openCreateTaskModal(): void {
    this.selectedTask.set(null);
    this.modalVisible.set(true);
  }

  /**
   * Abrir modal para editar tarea
   */
  openEditTaskModal(task: Task): void {
    this.selectedTask.set(task);
    this.modalVisible.set(true);
  }

  /**
   * Abrir modal de detalle de tarea
   */
  openDetailModal(task: Task): void {
    this.selectedTaskForDetail.set(task);
    this.detailModalVisible.set(true);
  }

  /**
   * Manejar guardado de tarea (create o update)
   */
  handleSaveTask(taskData: any): void {
    const isUpdate = !!this.selectedTask();

    const request$ = isUpdate
      ? this.taskService.updateTask(this.selectedTask()!.id, taskData)
      : this.taskService.createTask(taskData);

    request$.subscribe({
      next: (response) => {
        if (response.tipo === 1 || response.tipo === 1000) {
          this.messageService.add({
            severity: 'success',
            summary: 'Éxito',
            detail: response.mensajes?.[0] || `Tarea ${isUpdate ? 'actualizada' : 'creada'} correctamente`,
            life: 3000,
          });
          this.modalVisible.set(false);
          this.selectedTask.set(null);
          // Recargar tareas para actualizar la vista
          this.loadData();
        } else {
          this.messageService.add({
            severity: 'error',
            summary: 'Error',
            detail: response.mensajes?.[0] || 'Error al guardar la tarea',
            life: 5000,
          });
        }
      },
      error: (error) => {
        this.messageService.add({
          severity: 'error',
          summary: 'Error',
          detail: error.error?.mensajes?.[0] || 'Error de conexión',
          life: 5000,
        });
      },
    });
  }

  /**
   * Manejar eliminación de tarea
   */
  handleDeleteTask(taskId: number): void {
    this.confirmationService.confirm({
      message: '¿Estás seguro de eliminar esta tarea? Esta acción no se puede deshacer.',
      header: 'Confirmar Eliminación',
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Sí, eliminar',
      rejectLabel: 'Cancelar',
      acceptButtonStyleClass: 'p-button-danger',
      accept: () => {
        this.taskService.deleteTask(taskId).subscribe({
          next: (response) => {
            if (response.tipo === 1 || response.tipo === 1000) {
              this.messageService.add({
                severity: 'success',
                summary: 'Éxito',
                detail: response.mensajes?.[0] || 'Tarea eliminada correctamente',
                life: 3000,
              });
              this.modalVisible.set(false);
              this.selectedTask.set(null);
              // Recargar tareas para actualizar la vista
              this.loadData();
            } else {
              this.messageService.add({
                severity: 'error',
                summary: 'Error',
                detail: response.mensajes?.[0] || 'Error al eliminar la tarea',
                life: 5000,
              });
            }
          },
          error: (error) => {
            this.messageService.add({
              severity: 'error',
              summary: 'Error',
              detail: error.error?.mensajes?.[0] || 'Error de conexión',
              life: 5000,
            });
          },
        });
      },
    });
  }

  /**
   * Cerrar modal
   */
  handleCancelModal(): void {
    this.modalVisible.set(false);
    this.selectedTask.set(null);
  }
}
