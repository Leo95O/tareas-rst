import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { ConfirmationService, MessageService } from 'primeng/api';
import { AuthService } from '../../core/services/auth.service';
import { TaskService } from '../../core/services/task.service';
import { UserService } from '../../core/services/user.service';
import { ProjectService } from '../../core/services/project.service';
import { DataMasterService } from '../../core/services/data-master.service';
import { Task } from '../../core/interfaces/task.interface';

/**
 * MyTasksComponent - Vista de tareas asignadas al usuario actual
 * Refactorizado para usar DataMaster y Objetos Hidratados
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
  private readonly dataMaster = inject(DataMasterService);
  private readonly messageService = inject(MessageService);
  private readonly confirmationService = inject(ConfirmationService);

  // Signals base
  readonly currentUser = this.authService.currentUser;
  readonly tasksLoading = this.taskService.isLoading;

  // Catálogos (FUENTE ÚNICA)
  readonly proyectos = this.projectService.projects;
  readonly estados = this.dataMaster.estadosTarea;
  readonly prioridades = this.dataMaster.prioridades;
  readonly usuarios = this.userService.users;

  // ---------------------------
  // TAREAS AGRUPADAS POR ESTADO
  // ---------------------------
  readonly myTasksByEstado = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    if (!userId) return [];

    const allTasks = this.taskService.tasks();
    const estadosDisponibles = this.estados();

    if (!estadosDisponibles.length) return [];

    // Filtrar solo tareas asignadas al usuario actual
    // CORRECCIÓN: Se verifica tanto asignado_id como usuario_asignado_id por compatibilidad
    const myTasks = allTasks.filter(task => 
      (task.asignado_id ?? task.usuario_asignado_id) === userId
    );

    const estadosMap = new Map<number, { estado: any; tasks: Task[] }>();

    // Inicializar columnas de estados
    estadosDisponibles.forEach(estado => {
      estadosMap.set(estado.id, {
        estado,
        tasks: [],
      });
    });

    // Repartir tareas
    myTasks.forEach(task => {
      const estadoId = task.estado?.id ?? task.estado_id;
      if (estadoId !== undefined && estadosMap.has(estadoId)) {
        estadosMap.get(estadoId)!.tasks.push(task);
      }
    });

    // Retornar solo columnas que tengan tareas, ordenadas
    return Array.from(estadosMap.values())
      .sort((a, b) => (a.estado.orden ?? 0) - (b.estado.orden ?? 0))
      .filter(col => col.tasks.length > 0);
  });

  // ---------------------------
  // ESTADÍSTICAS PERSONALES
  // ---------------------------
  readonly totalMyTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    return this.taskService.tasks().filter(t => 
      (t.asignado_id ?? t.usuario_asignado_id) === userId
    ).length;
  });

  readonly completedTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    return this.taskService.tasks().filter(t =>
      (t.asignado_id ?? t.usuario_asignado_id) === userId &&
      (t.estado?.nombre || '').toLowerCase().includes('completada')
    ).length;
  });

  readonly pendingTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    const finalizados = ['completada', 'cancelada', 'finalizada'];
    return this.taskService.tasks().filter(t =>
      (t.asignado_id ?? t.usuario_asignado_id) === userId &&
      !finalizados.includes((t.estado?.nombre || '').toLowerCase())
    ).length;
  });

  readonly overdueTasks = computed(() => {
    const userId = this.currentUser()?.usuario_id;
    return this.taskService.tasks().filter(t =>
      (t.asignado_id ?? t.usuario_asignado_id) === userId && t.es_vencida === 1
    ).length;
  });

  // ---------------------------
  // MODALES
  // ---------------------------
  readonly modalVisible = signal(false);
  readonly selectedTask = signal<Task | null>(null);

  readonly detailModalVisible = signal(false);
  readonly selectedTaskForDetail = signal<Task | null>(null);

  ngOnInit(): void {
    this.loadData();
  }

  private loadData(): void {
    // Carga unificada de catálogos y tareas
    this.dataMaster.loadAll().subscribe(() => {
      this.taskService.getTasks();
    });

    this.projectService.getProjects();

    const rol = this.currentUser()?.rol_id;
    if (rol === 1 || rol === 2) {
      this.userService.getUsers();
    }
  }

  // ---------------------------
  // ACCIONES
  // ---------------------------
  openCreateTaskModal(): void {
    this.selectedTask.set(null);
    this.modalVisible.set(true);
  }

  openEditTaskModal(task: Task): void {
    this.selectedTask.set(task);
    this.modalVisible.set(true);
  }

  openDetailModal(task: Task): void {
    this.selectedTaskForDetail.set(task);
    this.detailModalVisible.set(true);
  }

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
          this.taskService.getTasks(); // Recargar datos
        }
      },
      error: (error) => {
        this.messageService.add({
          severity: 'error',
          summary: 'Error',
          detail: error.error?.mensajes?.[0] || 'Error de conexión',
          life: 5000,
        });
      }
    });
  }

  handleDeleteTask(taskId: number): void {
    this.confirmationService.confirm({
      message: '¿Estás seguro de eliminar esta tarea?',
      header: 'Confirmar eliminación',
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Eliminar',
      acceptButtonStyleClass: 'p-button-danger',
      accept: () => {
        this.taskService.deleteTask(taskId).subscribe(() => {
          this.messageService.add({
            severity: 'success',
            summary: 'Eliminado',
            detail: 'Tarea eliminada correctamente',
            life: 3000,
          });
          this.taskService.getTasks();
        });
      }
    });
  }

  handleCancelModal(): void {
    this.modalVisible.set(false);
    this.selectedTask.set(null);
  }
}