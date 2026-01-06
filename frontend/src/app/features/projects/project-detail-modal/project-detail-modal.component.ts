import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Project } from '../../../core/interfaces/project.interface';

/**
 * ProjectDetailModalComponent - Modal para mostrar detalle completo de un proyecto
 */
@Component({
    standalone: false,
    selector: 'app-project-detail-modal',
    templateUrl: './project-detail-modal.component.html',
    styleUrl: './project-detail-modal.component.scss',
})
export class ProjectDetailModalComponent {
    @Input() visible = false;
    @Input() project: Project | null = null;

    @Output() visibleChange = new EventEmitter<boolean>();
    @Output() onEdit = new EventEmitter<Project>();
    @Output() onClose = new EventEmitter<void>();

    closeModal(): void {
        this.visibleChange.emit(false);
        this.onClose.emit();
    }

    editProject(): void {
        if (this.project) {
            this.onEdit.emit(this.project);
            this.closeModal();
        }
    }

    /**
     * Obtener clases CSS para el badge de estado
     */
    getEstadoClasses(): string {
        const estado = (this.project?.estado_nombre || '').toLowerCase();

        if (estado.includes('planificación')) {
            return 'bg-indigo-100 text-indigo-700';
        }
        if (estado.includes('curso') || estado.includes('progreso')) {
            return 'bg-blue-100 text-blue-700';
        }
        if (estado.includes('detenido')) {
            return 'bg-yellow-100 text-yellow-700';
        }
        if (estado.includes('finalizado')) {
            return 'bg-green-100 text-green-700';
        }
        if (estado.includes('cancelado')) {
            return 'bg-red-100 text-red-700';
        }
        return 'bg-gray-100 text-gray-700';
    }

    /**
     * Obtener color hexadecimal del estado basado en el nombre
     */
    getEstadoColor(): string {
        const estado = (this.project?.estado_nombre || '').toLowerCase();

        if (estado.includes('planificación')) {
            return '#6366F1'; // indigo-500
        }
        if (estado.includes('curso') || estado.includes('progreso')) {
            return '#3B82F6'; // blue-500
        }
        if (estado.includes('detenido')) {
            return '#F59E0B'; // amber-500
        }
        if (estado.includes('finalizado')) {
            return '#10B981'; // emerald-500
        }
        if (estado.includes('cancelado')) {
            return '#EF4444'; // red-500
        }
        return '#6B7280'; // gray-500
    }

    /**
     * Formatear fecha de forma legible
     */
    formatDate(dateString: string | null): string {
        if (!dateString) return 'No especificada';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }

    /**
     * Obtener iniciales del proyecto
     */
    getProjectInitials(): string {
        if (!this.project?.nombre) return 'P';
        return this.project.nombre
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }
}
