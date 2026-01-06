import { Component, inject, signal, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { MenuItem } from '../../core/interfaces/admin.interface';

@Component({
  standalone: false,
  selector: 'app-admin-layout',
  templateUrl: './admin-layout.component.html',
  styleUrl: './admin-layout.component.scss',
})
export class AdminLayoutComponent implements OnInit {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  // Signals
  readonly drawerVisible = signal<boolean>(false);
  readonly currentUser = this.authService.currentUser;

  // Menú items
  menuItems: MenuItem[] = [];
  headerMenuItems: MenuItem[] = [];

  ngOnInit(): void {
    this.initializeMenu();
    this.initializeHeaderMenu();
  }

  /**
   * Inicializar items del drawer (sidebar)
   */
  private initializeMenu(): void {
    const userRoleId = this.currentUser()?.rol_id;
    const baseRoute = userRoleId === 2 ? '/project-manager' : '/admin'; // 2: PROJECT_MANAGER

    this.menuItems = [
      // MI ESPACIO - Área Personal
      {
        label: 'MI ESPACIO',
        icon: '',
        items: [
          {
            label: 'Inicio',
            icon: 'pi pi-home',
            route: `${baseRoute}/dashboard`,
          },
          {
            label: 'Mis Tareas',
            icon: 'pi pi-check-square',
            route: `${baseRoute}/mis-tareas`,
          },
        ],
      },
      // SUPERVISIÓN - Área de Negocio
      {
        label: 'SUPERVISIÓN',
        icon: '',
        items: [
          {
            label: 'Proyectos',
            icon: 'pi pi-forward',
            route: `${baseRoute}/proyectos`,
          },
          // Solo mostrar Tablero Equipo para PROJECT_MANAGER
          ...(userRoleId === 2 ? [{
            label: 'Tablero Tareas Equipo',
            icon: 'pi pi-table',
            route: `${baseRoute}/tablero-equipo`,
          }] : []),
        ],
      },
    ];

    // Agregar EQUIPO para PROJECT_MANAGER
    if (userRoleId === 2) {
      this.menuItems.push({
        label: 'EQUIPO',
        icon: '',
        items: [
          {
            label: 'Directorio',
            icon: 'pi pi-users',
            route: `${baseRoute}/directorio`,
          },
        ],
      });
    }

    // Solo mostrar ADMINISTRACIÓN y CONFIGURACIÓN para ADMIN
    if (userRoleId === 1) { // 1: ADMIN
      this.menuItems.push(
        // ADMINISTRACIÓN - Gestión de usuarios y roles
        {
          label: 'ADMINISTRACIÓN',
          icon: '',
          items: [
            {
              label: 'Equipo',
              icon: 'pi pi-users',
              route: '/admin/equipo',
            },
          ],
        },
        // CONFIGURACIÓN
        {
          label: 'CONFIGURACIÓN',
          icon: '',
          items: [
            {
              label: 'Niveles de Prioridad',
              icon: 'pi pi-bolt',
              route: '/admin/prioridades',
            },
          ],
        }
      );
    }
  }

  /**
   * Inicializar items del header menu
   */
  private initializeHeaderMenu(): void {
    this.headerMenuItems = [
      {
        label: this.currentUser()?.usuario_nombre || 'Usuario',
        icon: 'pi pi-user',
        items: [
          {
            label: 'Perfil',
            icon: 'pi pi-user-edit',
            command: () => this.router.navigate(['/admin/perfil']),
          },
          {
            label: 'Configuración',
            icon: 'pi pi-cog',
            command: () => this.router.navigate(['/admin/configuracion']),
          },
          {
            label: 'Cerrar Sesión',
            icon: 'pi pi-sign-out',
            command: () => this.logout(),
          },
        ],
      },
    ];
  }

  /**
   * Toggle drawer visibility
   */
  toggleDrawer(): void {
    this.drawerVisible.update((visible) => !visible);
  }

  /**
   * Navegar a una ruta y cerrar drawer
   */
  navigateTo(route?: string): void {
    if (route) {
      this.router.navigate([route]);
      this.drawerVisible.set(false);
    }
  }

  /**
   * Logout
   */
  logout(): void {
    this.authService.logout();
  }
}
