import { Component, Input, Output, EventEmitter, inject } from '@angular/core';
import { Task } from '../../../core/interfaces/task.interface';

/**
 * TaskDetailModalComponent - Modal para mostrar detalle completo de una tarea
 */
@Component({
    standalone: false,
    selector: 'app-task-detail-modal',
    templateUrl: './task-detail-modal.component.html',
    styleUrl: './task-detail-modal.component.scss',
})
export class TaskDetailModalComponent {
    @Input() visible = false;
    @Input() task: Task | null = null;

    @Output() visibleChange = new EventEmitter<boolean>();
    @Output() onEdit = new EventEmitter<Task>();
    @Output() onClose = new EventEmitter<void>();

    closeModal(): void {
        this.visibleChange.emit(false);
        this.onClose.emit();
    }

    editTask(): void {
        if (this.task) {
            this.onEdit.emit(this.task);
            this.closeModal();
        }
    }

    /**
     * Obtener clases CSS para el badge de prioridad
     */
    getPriorityClasses(): string {
        const prioridad = (this.task?.prioridad || '').toLowerCase();

        if (prioridad.includes('crítica')) {
            return 'bg-red-500 text-white';
        }
        if (prioridad.includes('alta')) {
            return 'bg-orange-500 text-white';
        }
        if (prioridad.includes('media')) {
            return 'bg-yellow-500 text-white';
        }
        return 'bg-blue-500 text-white';
    }

    /**
     * Obtener color hexadecimal de prioridad basado en el nombre
     */
    getPriorityColor(): string {
        const prioridad = (this.task?.prioridad || '').toLowerCase();

        if (prioridad.includes('crítica')) {
            return '#991B1B'; // red-800
        }
        if (prioridad.includes('alta')) {
            return '#DC2626'; // red-600
        }
        if (prioridad.includes('media')) {
            return '#F59E0B'; // amber-500
        }
        if (prioridad.includes('baja')) {
            return '#10B981'; // emerald-500
        }
        return '#6B7280'; // gray-500 fallback
    }

    /**
     * Obtener clases CSS para el badge de estado
     */
    getEstadoClasses(): string {
        const estado = (this.task?.estado || '').toLowerCase();

        if (estado.includes('pendiente')) {
            return 'bg-gray-100 text-gray-700';
        }
        if (estado.includes('progreso')) {
            return 'bg-blue-100 text-blue-700';
        }
        if (estado.includes('revisión')) {
            return 'bg-yellow-100 text-yellow-700';
        }
        if (estado.includes('completada')) {
            return 'bg-green-100 text-green-700';
        }
        if (estado.includes('cancelada')) {
            return 'bg-red-100 text-red-700';
        }
        return 'bg-gray-100 text-gray-700';
    }

    /**
     * Formatear fecha de forma legible
     */
    formatDate(dateString: string | null): string {
        if (!dateString) return 'Sin fecha';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Verificar si la tarea está vencida
     */
    isOverdue(): boolean {
        return this.task?.es_vencida === 1;
    }
}
