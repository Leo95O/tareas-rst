import { Component, Input, Output, EventEmitter } from '@angular/core';
import { Task } from '../../../core/interfaces/task.interface';

/**
 * TaskCardComponent - Componente reutilizable para mostrar tarjetas de tareas
 * Corregido para manejar Objetos Hidratados (Plantilla Dorada)
 */
@Component({
  standalone: false,
  selector: 'app-task-card',
  templateUrl: './task-card.component.html',
  styleUrl: './task-card.component.scss',
})
export class TaskCardComponent {
  @Input({ required: true }) task!: Task;
  @Output() onEdit = new EventEmitter<Task>();
  @Output() onClick = new EventEmitter<Task>();

  /**
   * Emitir evento de click para abrir detalle
   */
  openDetail(): void {
    this.onClick.emit(this.task);
  }

  /**
   * Emitir evento de edición
   */
  editTask(): void {
    this.onEdit.emit(this.task);
  }

  /**
   * Obtener color hexadecimal del badge de prioridad basado en el nombre
   */
  getPriorityColor(): string {
    // CORRECCIÓN: Accedemos a ?.nombre porque prioridad ahora es un objeto
    const prioridad = (this.task.prioridad?.nombre || '').toLowerCase();

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
   * Obtener icono de prioridad
   */
  getPriorityIcon(): string {
    // CORRECCIÓN: Acceso seguro al nombre del objeto
    const prioridad = (this.task.prioridad?.nombre || '').toLowerCase();

    if (prioridad.includes('crítica')) {
      return 'pi-exclamation-triangle';
    }
    if (prioridad.includes('alta')) {
      return 'pi-arrow-up';
    }
    if (prioridad.includes('media')) {
      return 'pi-minus';
    }
    return 'pi-arrow-down';
  }

  /**
   * Obtener clases CSS para el badge del estado
   */
  getEstadoBadgeClasses(): string {
    // CORRECCIÓN: Acceso seguro al nombre del objeto
    const estado = (this.task.estado?.nombre || '').toLowerCase();

    if (estado.includes('pendiente')) {
      return 'bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700';
    }
    if (estado.includes('progreso')) {
      return 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700';
    }
    if (estado.includes('revisión')) {
      return 'bg-gradient-to-r from-yellow-50 to-yellow-100 text-yellow-700';
    }
    if (estado.includes('completada')) {
      return 'bg-gradient-to-r from-green-50 to-green-100 text-green-700';
    }
    if (estado.includes('cancelada')) {
      return 'bg-gradient-to-r from-red-50 to-red-100 text-red-700';
    }

    return 'bg-gradient-to-r from-gray-50 to-gray-100 text-gray-700';
  }

  /**
   * Verificar si la tarea está vencida (usa campo calculado del backend)
   */
  isOverdue(): boolean {
    return this.task.es_vencida === 1;
  }

  /**
   * Formatear fecha de vencimiento de forma descriptiva
   */
  formatDate(dateString: string | null | undefined): string { // Agregado undefined por seguridad
    if (!dateString) return '';

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = date.getTime() - now.getTime();
    const diffAbsMs = Math.abs(diffMs);

    // Calcular diferencias
    const diffDays = Math.floor(diffAbsMs / (1000 * 60 * 60 * 24));
    const diffHours = Math.floor(diffAbsMs / (1000 * 60 * 60));

    // Si ya venció (fecha en el pasado)
    if (diffMs < 0) {
      if (diffDays > 0) return `Venció hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;
      if (diffHours > 0) return `Venció hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
      return 'Vencida';
    }

    // Fecha futura
    if (diffDays >= 1) {
      return `Vence en ${diffDays} día${diffDays > 1 ? 's' : ''}`;
    }

    if (diffHours >= 1) {
      return `Vence en ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
    }

    // Menos de 1 hora: mostrar cuenta regresiva HH:MM:SS
    const hours = Math.floor(diffAbsMs / (1000 * 60 * 60));
    const minutes = Math.floor((diffAbsMs % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diffAbsMs % (1000 * 60)) / 1000);

    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
  }
}