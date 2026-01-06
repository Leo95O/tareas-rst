import { Component, inject, signal, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../core/services/auth.service';
import { MenuItem } from '../../core/interfaces/admin.interface';

/*
 * UserLayoutComponent - Layout para usuarios normales (rol_id = 3)
*/
@Component({
    standalone: false,
    selector: 'app-user-layout',
    templateUrl: './user-layout.component.html',
    styleUrl: './user-layout.component.scss',
})
export class UserLayoutComponent implements OnInit {
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
     * Inicializar items del drawer (sidebar) - Menú simplificado para usuario
     */
    private initializeMenu(): void {
        this.menuItems = [
            {
                label: 'MI ESPACIO',
                icon: '',
                items: [
                    {
                        label: 'Mis Tareas',
                        icon: 'pi pi-check-square',
                        route: '/user/mis-tareas',
                    },
                ],
            },
        ];
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
