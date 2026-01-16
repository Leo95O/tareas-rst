import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Task } from '../../../core/interfaces/task.interface';

@Component({
  standalone: false,
  selector: 'app-task-detail-modal',
  templateUrl: './task-detail-modal.component.html',
  styleUrl: './task-detail-modal.component.scss'
})
export class TaskDetailModalComponent {
  @Input() visible: boolean = false;
  @Input() task: Task | null = null;
  @Output() onClose = new EventEmitter<void>();
  @Output() onEdit = new EventEmitter<Task>();

  // CORRECCIÓN: Método para cerrar el modal solicitado por el HTML
  closeModal(): void {
    this.onClose.emit();
  }

  editTask(): void {
    if (this.task) {
      this.onEdit.emit(this.task);
      this.closeModal();
    }
  }

  // CORRECCIÓN: Método solicitado para clases de estado
  getEstadoClasses(): string {
    const estado = (this.task?.estado?.nombre || '').toLowerCase();
    if (estado.includes('completada')) return 'bg-green-100 text-green-700';
    if (estado.includes('progreso')) return 'bg-blue-100 text-blue-700';
    return 'bg-gray-100 text-gray-700';
  }

  // CORRECCIÓN: Método solicitado para color de prioridad
  getPriorityColor(): string {
    const prioridad = (this.task?.prioridad?.nombre || '').toLowerCase();
    if (prioridad.includes('crítica')) return '#991B1B';
    if (prioridad.includes('alta')) return '#DC2626';
    if (prioridad.includes('media')) return '#F59E0B';
    return '#10B981';
  }

  // CORRECCIÓN: Método solicitado para verificar vencimiento
  isOverdue(): boolean {
    return this.task?.es_vencida === 1;
  }

  formatDate(date: string | null | undefined): string {
    if (!date) return 'No definida';
    return new Date(date).toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
}