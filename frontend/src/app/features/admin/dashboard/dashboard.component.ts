import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { ConfirmationService, MessageService } from 'primeng/api';
import { AdminService } from '../../../core/services/admin.service';
import { TaskService } from '../../../core/services/task.service';
import { UserService } from '../../../core/services/user.service';
import { Task } from '../../../core/interfaces/task.interface';
import { ProjectService } from '../../../core/services/project.service';
import { DashboardService } from '../../../core/services/dashboard.service';
import { DashboardReport } from '../../../core/interfaces/report.interface';

@Component({
  standalone: false,
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.scss',
})
export class DashboardComponent implements OnInit {
  private readonly adminService = inject(AdminService);
  private readonly taskService = inject(TaskService);
  private readonly userService = inject(UserService);
  private readonly projectService = inject(ProjectService);
  private readonly dashboardService = inject(DashboardService);
  private readonly messageService = inject(MessageService);
  private readonly confirmationService = inject(ConfirmationService);

  // Signals desde los servicios
  readonly tasksByEstado = this.taskService.tasksByEstado;
  readonly tasksLoading = this.taskService.isLoading;
  readonly errorMessage = signal<string>('');

  // Reporte del Dashboard
  readonly dashboardReport = signal<DashboardReport | null>(null);
  readonly reportLoading = signal<boolean>(false);

  // Modal de tarea (edición)
  modalVisible = signal<boolean>(false);
  selectedTask = signal<Task | null>(null);

  // Modal de detalle
  detailModalVisible = signal<boolean>(false);
  selectedTaskForDetail = signal<Task | null>(null);

  // Catálogos para el modal
  readonly proyectos = this.projectService.projects;
  readonly estados = this.taskService.estados;
  readonly prioridades = this.taskService.prioridades;
  readonly usuarios = this.userService.users;

  // Computed signals para estadísticas 
  readonly totalProyectos = computed(() => this.dashboardReport()?.resumen.total_proyectos ?? 0);
  readonly totalTareas = computed(() => this.dashboardReport()?.resumen.total_tareas ?? 0);
  readonly tareasCompletadas = computed(() => {
    const report = this.dashboardReport();
    if (!report) return 0;
    const completadasState = report.grafico_estados.find(e => e.estado_nombre.toLowerCase() === 'completada');
    return completadasState ? completadasState.cantidad : 0;
  });

  ngOnInit(): void {
    // Cargar catálogos primero (incluyendo estados)
    this.loadCatalogos();
    // Luego cargar tareas (que dependen de los estados para el Kanban)
    setTimeout(() => {
      this.loadTasks();
      this.loadDashboardData();
    }, 500);
  }

  private loadDashboardData(): void {
    this.reportLoading.set(true);

    this.dashboardService.getDashboardData().subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.dashboardReport.set(response.data);
        }
        this.reportLoading.set(false);
      },
      error: () => {
        this.reportLoading.set(false);
      }
    });
  }

  /**
   * Cargar catalogo de tareas y estados
   */
  private loadCatalogos(): void {
    this.projectService.getProjects();
    this.taskService.getEstados();
    this.taskService.getPrioridades();
    this.userService.getUsers();
  }

  /**
   * Cargar tareas
   */
  private loadTasks(): void {
    this.taskService.getTasks();
  }

  /**
   * Recargar datos
   */
  refresh(): void {
    this.errorMessage.set('');
    this.loadTasks();
    this.loadDashboardData();
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
    const isEdit = !!this.selectedTask();

    const request = isEdit
      ? this.taskService.updateTask(this.selectedTask()!.id, taskData)
      : this.taskService.createTask(taskData);

    request.subscribe({
      next: (response) => {
        if (response.tipo === 1 || response.tipo === 1000) {
          this.messageService.add({
            severity: 'success',
            summary: isEdit ? 'Tarea actualizada' : 'Tarea creada',
            detail: response.mensajes?.[0] || 'Operación exitosa',
            life: 3000,
          });
          this.modalVisible.set(false);
          this.selectedTask.set(null);
          // Recargar tareas para actualizar la vista
          this.loadTasks();
          this.loadDashboardData();
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
   * Cerrar modal
   */
  handleCancelModal(): void {
    this.modalVisible.set(false);
    this.selectedTask.set(null);
  }

  /**
   * Manejar eliminación de tarea con confirmación
   */
  handleDeleteTask(taskId: number): void {
    this.confirmationService.confirm({
      message: '¿Estás seguro de eliminar esta tarea? Esta acción no se puede deshacer.',
      header: 'Confirmar eliminación',
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Eliminar',
      rejectLabel: 'Cancelar',
      acceptButtonStyleClass: 'p-button-danger',
      accept: () => {
        this.taskService.deleteTask(taskId).subscribe({
          next: (response) => {
            if (response.tipo === 1 || response.tipo === 1000) {
              this.messageService.add({
                severity: 'success',
                summary: 'Éxito',
                detail: 'Tarea eliminada correctamente',
                life: 3000,
              });
              this.modalVisible.set(false);
              this.selectedTask.set(null);
              // Recargar tareas para actualizar la vista
              this.loadTasks();
              this.loadDashboardData();
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

  // Drag & Drop state
  draggedTask: Task | null = null;
  isDraggingOver: number | null = null;

  /**
   * Drag & Drop: Inicio del arrastre
   */
  onDragStart(event: DragEvent, task: Task): void {
    this.draggedTask = task;
    if (event.dataTransfer) {
      event.dataTransfer.effectAllowed = 'move';
    }
  }

  /**
   * Drag & Drop: Fin del arrastre
   */
  onDragEnd(event: DragEvent): void {
    this.draggedTask = null;
    this.isDraggingOver = null;
  }

  /**
   * Drag & Drop: Sobre la zona de drop
   */
  onDragOver(event: DragEvent, estadoId: number): void {
    event.preventDefault();
    if (event.dataTransfer) {
      event.dataTransfer.dropEffect = 'move';
    }
    this.isDraggingOver = estadoId;
  }

  /**
   * Drag & Drop: Salir de la zona de drop
   */
  onDragLeave(event: DragEvent): void {
    this.isDraggingOver = null;
  }

  /**
   * Drag & Drop: Soltar en la zona
   */
  onDrop(event: DragEvent, nuevoEstadoId: number): void {
    event.preventDefault();
    this.isDraggingOver = null;

    if (!this.draggedTask) return;

    // Si el estado es el mismo, no hacer nada
    if (this.draggedTask.estado_id === nuevoEstadoId) {
      this.draggedTask = null;
      return;
    }

    // Actualizar la tarea con el nuevo estado
    const taskData = {
      titulo: this.draggedTask.titulo,
      descripcion: this.draggedTask.descripcion,
      proyecto_id: this.draggedTask.proyecto_id,
      prioridad_id: this.draggedTask.prioridad_id,
      estado_id: nuevoEstadoId,
      usuario_asignado: this.draggedTask.usuario_asignado_id,
      fecha_limite: this.draggedTask.fecha_limite,
    };

    this.taskService.updateTask(this.draggedTask.id, taskData).subscribe({
      next: (response) => {
        if (response.tipo === 1 || response.tipo === 1000) {
          this.messageService.add({
            severity: 'success',
            summary: 'Tarea movida',
            detail: 'Estado actualizado correctamente',
            life: 2000,
          });
        }
      },
      error: (error) => {
        this.messageService.add({
          severity: 'error',
          summary: 'Error',
          detail: 'No se pudo mover la tarea',
          life: 3000,
        });
        // Recargar tareas para revertir el cambio visual
        this.loadTasks();
      },
    });

    this.draggedTask = null;
  }
}
