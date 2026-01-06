import { Component, inject, OnInit } from '@angular/core';
import { MessageService, ConfirmationService } from 'primeng/api';
import { TaskService } from '../../../core/services/task.service';
import { Task } from '../../../core/interfaces/task.interface';

/**
 * TaskPoolComponent - Vista de "Bolsa de Tareas"
 *
 * Muestra tareas sin asignar (usuario_asignado IS NULL)
 * que cualquier usuario puede tomarse.
 */
@Component({
    standalone: false,
    selector: 'app-task-pool',
    templateUrl: './task-pool.component.html',
    styleUrl: './task-pool.component.scss',
})
export class TaskPoolComponent implements OnInit {
    private readonly taskService = inject(TaskService);
    private readonly messageService = inject(MessageService);
    private readonly confirmationService = inject(ConfirmationService);

    // Signals desde el servicio
    readonly taskPool = this.taskService.taskPool;
    readonly isLoading = this.taskService.isLoading;
    readonly errorMessage = this.taskService.errorMessage;

    ngOnInit(): void {
        this.loadTaskPool();
    }

    /**
     * Cargar tareas disponibles
     */
    loadTaskPool(): void {
        this.taskService.getTaskPool();
    }

    /**
     * Asignarse una tarea
     */
    assignTask(task: Task): void {
        this.confirmationService.confirm({
            message: `¿Deseas asignarte la tarea "${task.titulo}"? Una vez asignada, aparecerá en tu lista de "Mis Tareas".`,
            header: 'Confirmar Asignación',
            icon: 'pi pi-user-plus',
            acceptLabel: 'Sí, asignarme',
            rejectLabel: 'Cancelar',
            acceptButtonStyleClass: 'p-button-success',
            accept: () => {
                this.taskService.assignTaskToMe(task.id).subscribe({
                    next: (response) => {
                        if (response.tipo === 1 || response.tipo === 1000) {
                            this.messageService.add({
                                severity: 'success',
                                summary: '¡Tarea asignada!',
                                detail: response.mensajes?.[0] || 'La tarea ahora es tuya. Revisa "Mis Tareas".',
                                life: 4000,
                            });
                            // Recargar la bolsa para quitar la tarea
                            this.loadTaskPool();
                        } else {
                            this.messageService.add({
                                severity: 'warn',
                                summary: 'No se pudo asignar',
                                detail: response.mensajes?.[0] || 'Intenta de nuevo',
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
     * Obtener clase CSS según prioridad
     */
    getPrioridadClass(prioridad: string | undefined): string {
        if (!prioridad) return 'priority-default';

        const map: Record<string, string> = {
            'Crítica': 'priority-critica',
            'Alta': 'priority-alta',
            'Media': 'priority-media',
            'Baja': 'priority-baja',
        };
        return map[prioridad] || 'priority-default';
    }
}
