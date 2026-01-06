import { Component, computed, inject, Input, OnInit, signal } from '@angular/core';
import { ConfirmationService, MessageService } from 'primeng/api';
import { UserService } from '../../core/services/user.service';
import { User } from '../../core/interfaces/user.interface';

/**
 * TeamComponent - Vista de gestión de equipo (usuarios)
 *
 * Features:
 * - Lista todos los usuarios del sistema (solo ADMIN)
 * - CRUD completo de usuarios
 * - Cambio de rol (gestión de permisos)
 * - Activar/desactivar usuarios
 * - Diseño tipo tabla con acciones
 * - Modo solo lectura (readOnly) para Project Managers
 */
@Component({
  standalone: false,
  selector: 'app-team',
  templateUrl: './team.component.html',
  styleUrl: './team.component.scss',
})
export class TeamComponent implements OnInit {
  private readonly userService = inject(UserService);
  private readonly messageService = inject(MessageService);
  private readonly confirmationService = inject(ConfirmationService);

  /**
   * Modo solo lectura - oculta botones de acciones
   */
  @Input() readOnly: boolean = false;

  // Signals desde el servicio
  readonly users = this.userService.users;
  readonly roles = this.userService.roles;
  readonly isLoading = this.userService.isLoading;

  // Estadísticas computadas
  readonly totalUsers = computed(() => this.users().length);

  readonly activeUsers = computed(() =>
    this.users().filter(user => user.usuario_estado === 1).length
  );

  readonly inactiveUsers = computed(() =>
    this.users().filter(user => user.usuario_estado === 0).length
  );

  readonly adminCount = computed(() =>
    this.users().filter(user => user.rol_id === 1).length
  );

  // Modal state
  readonly modalVisible = signal<boolean>(false);
  readonly selectedUser = signal<User | null>(null);

  ngOnInit(): void {
    this.loadData();
  }

  /**
   * Cargar datos iniciales
   */
  private loadData(): void {
    this.userService.getUsers();
    this.userService.getRoles();
  }

  /**
   * Abrir modal para crear usuario
   */
  openCreateModal(): void {
    this.selectedUser.set(null);
    this.modalVisible.set(true);
  }

  /**
   * Abrir modal para editar usuario
   */
  openEditModal(user: User): void {
    this.selectedUser.set(user);
    this.modalVisible.set(true);
  }

  /**
   * Manejar guardado de usuario (create o update)
   */
  handleSaveUser(userData: any): void {
    const isUpdate = !!this.selectedUser();

    const request$ = isUpdate
      ? this.userService.updateUser(this.selectedUser()!.usuario_id, userData)
      : this.userService.createUser(userData);

    request$.subscribe({
      next: (response) => {
        if (response.tipo === 1 || response.tipo === 1000) {
          this.messageService.add({
            severity: 'success',
            summary: 'Éxito',
            detail: response.mensajes?.[0] || `Usuario ${isUpdate ? 'actualizado' : 'creado'} correctamente`,
            life: 3000,
          });
          this.modalVisible.set(false);
          this.selectedUser.set(null);
        } else {
          this.messageService.add({
            severity: 'error',
            summary: 'Error',
            detail: response.mensajes?.[0] || 'Error al guardar el usuario',
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
   * Manejar eliminación de usuario
   */
  /*handleDeleteUser(user: User): void {
    this.confirmationService.confirm({
      message: `¿Estás seguro de eliminar al usuario ${user.usuario_nombre}? Esta acción no se puede deshacer.`,
      header: 'Confirmar Eliminación',
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Sí, eliminar',
      rejectLabel: 'Cancelar',
      acceptButtonStyleClass: 'p-button-danger',
      accept: () => {
        this.userService.deleteUser(user.usuario_id).subscribe({
          next: (response) => {
            if (response.tipo === 1 || response.tipo === 1000) {
              this.messageService.add({
                severity: 'success',
                summary: 'Éxito',
                detail: response.mensajes?.[0] || 'Usuario eliminado correctamente',
                life: 3000,
              });
            } else {
              this.messageService.add({
                severity: 'error',
                summary: 'Error',
                detail: response.mensajes?.[0] || 'Error al eliminar el usuario',
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
  }*/

  /**
   * Cambiar estado activo/inactivo de usuario
   */
  toggleUserStatus(user: User): void {
    const newStatus = user.usuario_estado === 0; // Toggle logic: if 0 (inactive), make it 1 (active)
    const action = newStatus ? 'activar' : 'desactivar';

    this.confirmationService.confirm({
      message: `¿Estás seguro de ${action} al usuario ${user.usuario_nombre}?`,
      header: `Confirmar ${action.charAt(0).toUpperCase() + action.slice(1)}`,
      icon: 'pi pi-question-circle',
      acceptLabel: 'Sí, confirmar',
      rejectLabel: 'Cancelar',
      accept: () => {
        this.userService.toggleUserStatus(user.usuario_id, newStatus).subscribe({
          next: (response) => {
            if (response.tipo === 1 || response.tipo === 1000) {
              this.messageService.add({
                severity: 'success',
                summary: 'Éxito',
                detail: `Usuario ${action}do correctamente`,
                life: 3000,
              });
            } else {
              this.messageService.add({
                severity: 'error',
                summary: 'Error',
                detail: response.mensajes?.[0] || `Error al ${action} el usuario`,
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
    this.selectedUser.set(null);
  }

  /**
   * Obtener badge de rol con color
   */
  getRoleBadgeClass(roleId: number): string {
    const badges: Record<number, string> = {
      1: 'bg-red-100 text-red-700 border-red-200', // ADMIN
      2: 'bg-blue-100 text-blue-700 border-blue-200', // PM
      3: 'bg-green-100 text-green-700 border-green-200', // USER
    };
    return badges[roleId] || 'bg-gray-100 text-gray-700 border-gray-200';
  }

  getRoleName(roleId: number): string {
    const roles: Record<number, string> = {
      1: 'ADMIN',
      2: 'PROJECT MANAGER',
      3: 'USER'
    };
    return roles[roleId] || 'UNKNOWN';
  }

  /**
   * Formatear fecha
   */
  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
}
