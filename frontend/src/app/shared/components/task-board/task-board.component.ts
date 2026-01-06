import { Component, computed, inject, OnInit } from '@angular/core';
import { TaskService } from '../../../core/services/task.service';
import { AuthService } from '../../../core/services/auth.service';
import { Task } from '../../../core/interfaces/task.interface';

/**
 * TaskBoardComponent - Tablero Kanban de tareas agrupadas por estado
 * 
 * Features:
 * - Vista tipo Kanban con columnas por estado
 * - Filtrado automático según rol (PM solo ve tareas de sus proyectos)
 * - Cards reutilizando TaskCardComponent
 * - Contador de tareas por columna
 */
@Component({
  standalone: false,
  selector: 'app-task-board',
  templateUrl: './task-board.component.html',
  styleUrl: './task-board.component.scss',
})
export class TaskBoardComponent implements OnInit {
  private readonly taskService = inject(TaskService);
  private readonly authService = inject(AuthService);

  readonly tasks = this.taskService.tasks;
  readonly estados = this.taskService.estados;
  readonly currentUser = this.authService.currentUser;

  // Estadísticas computadas
  readonly totalTareas = computed(() => this.tasks().length);

  readonly tareasCompletadas = computed(() => {
    return this.tasks().filter(t => (t.estado || '').toLowerCase() === 'completada').length;
  });

  readonly tareasVencidas = computed(() => {
    return this.tasks().filter(t => t.es_vencida === 1).length;
  });

  // Tareas agrupadas por estado
  readonly tasksByEstado = computed(() => {
    const allTasks = this.tasks();
    const estados = this.estados();

    // Agrupar tareas por estado (convertir estado_id a number para comparar)
    return estados.map(estado => ({
      estado: estado,
      tasks: allTasks.filter(task => task.estado_id === Number(estado.estado_id)),
    }));
  });

  ngOnInit(): void {
    this.loadData();
  }

  private loadData(): void {
    this.taskService.getTasks();
    this.taskService.getEstados();
  }

  /**
   * Manejar click en tarea (emitir evento para que el padre maneje)
   */
  handleTaskClick(task: Task): void {
    // Por ahora solo log, en el futuro se puede agregar Output event
  }
}
