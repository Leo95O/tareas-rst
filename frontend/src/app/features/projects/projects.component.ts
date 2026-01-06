import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { ConfirmationService, MessageService } from 'primeng/api';
import { ProjectService } from '../../core/services/project.service';
import { AuthService } from '../../core/services/auth.service';
import { Project } from '../../core/interfaces/project.interface';

@Component({
  standalone: false,
  selector: 'app-projects',
  templateUrl: './projects.component.html',
  styleUrl: './projects.component.scss',
})
export class ProjectsComponent implements OnInit {
  private readonly projectService = inject(ProjectService);
  private readonly authService = inject(AuthService);
  private readonly messageService = inject(MessageService);
  private readonly confirmationService = inject(ConfirmationService);

  // Signals desde el servicio
  readonly projects = this.projectService.projects;
  readonly isLoading = this.projectService.isLoading;
  readonly currentUser = this.authService.currentUser;

  readonly totalProjects = computed(() => this.projects().length);

  readonly recentProjects = computed(() => {
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

    return this.projects().filter(
      project => new Date(project.fecha_creacion) >= thirtyDaysAgo
    ).length;
  });

  readonly activeProjects = computed(() => {
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);

    return this.projects().filter(
      project => new Date(project.fecha_creacion) >= sevenDaysAgo
    ).length;
  });

  // Modal state (edición)
  readonly modalVisible = signal<boolean>(false);
  readonly selectedProject = signal<Project | null>(null);

  // Modal de detalle
  readonly detailModalVisible = signal<boolean>(false);
  readonly selectedProjectForDetail = signal<Project | null>(null);

  ngOnInit(): void {
    this.loadProjects();
  }

  /**
   * Cargar proyectos
   */
  private loadProjects(): void {
    this.projectService.getProjects();
  }

  /**
   * Abrir modal para crear proyecto
   */
  openCreateModal(): void {
    this.selectedProject.set(null);
    this.modalVisible.set(true);
  }

  /**
   * Abrir modal para editar proyecto
   */
  openEditModal(project: Project): void {
    this.selectedProject.set(project);
    this.modalVisible.set(true);
  }

  /**
   * Abrir modal de detalle de proyecto
   */
  openDetailModal(project: Project): void {
    this.selectedProjectForDetail.set(project);
    this.detailModalVisible.set(true);
  }

  /**
   * Manejar guardado de proyecto (create o update)
   */
  handleSaveProject(projectData: any): void {
    const isUpdate = !!this.selectedProject();

    const request$ = isUpdate
      ? this.projectService.updateProject(this.selectedProject()!.id, projectData)
      : this.projectService.createProject(projectData);

    request$.subscribe({
      next: (response) => {
        if (response.tipo === 1 || response.tipo === 1000) {
          this.messageService.add({
            severity: 'success',
            summary: 'Éxito',
            detail: response.mensajes?.[0] || `Proyecto ${isUpdate ? 'actualizado' : 'creado'} correctamente`,
            life: 3000,
          });
          this.modalVisible.set(false);
          this.selectedProject.set(null);
        } else {
          this.messageService.add({
            severity: 'error',
            summary: 'Error',
            detail: response.mensajes?.[0] || 'Error al guardar el proyecto',
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
   * Manejar eliminación de proyecto
   */
  handleDeleteProject(projectId: number): void {
    // Verificar permisos antes de mostrar confirmación
    const project = this.projects().find(p => p.id === projectId);
    const currentUserId = this.currentUser()?.usuario_id;
    const userRoleId = this.currentUser()?.rol_id;

    // PROJECT_MANAGER solo puede eliminar sus propios proyectos
    if (userRoleId === 2 && project?.creador_id !== currentUserId) {
      this.messageService.add({
        severity: 'warn',
        summary: 'Permiso Denegado',
        detail: 'Solo puedes eliminar proyectos que tú creaste',
        life: 5000,
      });
      return;
    }

    this.confirmationService.confirm({
      message: '¿Estás seguro de eliminar este proyecto? Esta acción no se puede deshacer y eliminará todas las tareas asociadas.',
      header: 'Confirmar Eliminación',
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'Sí, eliminar',
      rejectLabel: 'Cancelar',
      acceptButtonStyleClass: 'p-button-danger',
      accept: () => {
        this.projectService.deleteProject(projectId).subscribe({
          next: (response) => {
            if (response.tipo === 1 || response.tipo === 1000) {
              this.messageService.add({
                severity: 'success',
                summary: 'Éxito',
                detail: response.mensajes?.[0] || 'Proyecto eliminado correctamente',
                life: 3000,
              });
              this.modalVisible.set(false);
              this.selectedProject.set(null);
            } else {
              this.messageService.add({
                severity: 'error',
                summary: 'Error',
                detail: response.mensajes?.[0] || 'Error al eliminar el proyecto',
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
    this.selectedProject.set(null);
  }

  /**
   * Formatear fecha para mostrar
   */
  formatDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Hoy';
    if (diffDays === 1) return 'Ayer';
    if (diffDays < 7) return `Hace ${diffDays} días`;
    if (diffDays < 30) return `Hace ${Math.floor(diffDays / 7)} semanas`;
    if (diffDays < 365) return `Hace ${Math.floor(diffDays / 30)} meses`;
    return date.toLocaleDateString('es-ES');
  }

  /**
   * Obtener iniciales del nombre del proyecto
   */
  getProjectInitials(name: string): string {
    return name
      .split(' ')
      .map(word => word[0])
      .join('')
      .toUpperCase()
      .substring(0, 2);
  }

  /**
   * Verificar si el usuario puede eliminar el proyecto
   */
  canDeleteProject(project: Project): boolean {
    const currentUserId = this.currentUser()?.usuario_id;
    const userRoleId = this.currentUser()?.rol_id;

    // ADMIN puede eliminar cualquier proyecto
    if (userRoleId === 1) return true;

    // PROJECT_MANAGER solo puede eliminar sus propios proyectos
    if (userRoleId === 2) {
      return project.creador_id === currentUserId;
    }

    return false;
  }

  /**
   * Obtener color basado en el nombre (consistente)
   */
  getProjectColor(name: string): string {
    const colors = [
      'bg-blue-500',
      'bg-green-500',
      'bg-purple-500',
      'bg-orange-500',
      'bg-pink-500',
      'bg-indigo-500',
      'bg-teal-500',
      'bg-red-500',
    ];

    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
  }
}
