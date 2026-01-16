import { Component, computed, inject, OnInit } from '@angular/core';
import { TaskService } from '../../../core/services/task.service';
import { AuthService } from '../../../core/services/auth.service';
import { DataMasterService } from '../../../core/services/data-master.service'; // <--- 1. Importar
import { Task } from '../../../core/interfaces/task.interface';

/**
 * TaskBoardComponent - Tablero Kanban de tareas agrupadas por estado
 * Refactorizado para usar DataMaster y señales del TaskService
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
  private readonly dataMaster = inject(DataMasterService); // <--- 2. Inyectar

  readonly tasks = this.taskService.tasks;
  
  // 3. Usamos los estados del DataMaster (Fuente única)
  readonly estados = this.dataMaster.estadosTarea; 
  
  readonly currentUser = this.authService.currentUser;

  // Estadísticas computadas
  readonly totalTareas = computed(() => this.tasks().length);

  readonly tareasCompletadas = computed(() => {
    // 4. CORRECCIÓN: Acceder a t.estado?.nombre de forma segura
    return this.tasks().filter(t => (t.estado?.nombre || '').toLowerCase().includes('completada')).length;
  });

  readonly tareasVencidas = computed(() => {
    return this.tasks().filter(t => t.es_vencida === 1).length;
  });

  // 5. Usamos la agrupación que ya hace el servicio (Centralizamos la lógica)
  readonly tasksByEstado = this.taskService.tasksByEstado;

  ngOnInit(): void {
    this.loadData();
  }

  private loadData(): void {
    // Solo pedimos las tareas. El servicio (getTasks) ya se encarga 
    // de verificar si los catálogos del DataMaster están cargados.
    this.taskService.getTasks();
  }

  /**
   * Manejar click en tarea (emitir evento para que el padre maneje)
   */
  handleTaskClick(task: Task): void {
    // Por ahora solo log, en el futuro se puede agregar Output event
    console.log('Click en tarea:', task);
  }
}