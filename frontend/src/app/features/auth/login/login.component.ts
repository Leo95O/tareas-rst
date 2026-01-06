import { Component, inject, signal } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MessageService } from 'primeng/api';

// Services
import { AuthService } from '../../../core/services/auth.service';

@Component({
  standalone: false,
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss',
  providers: [MessageService],
})
export class LoginComponent {
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly messageService = inject(MessageService);

  // Signals
  readonly isLoading = signal<boolean>(false);
  readonly showPassword = signal<boolean>(false);

  // Formulario reactivo
  loginForm: FormGroup = this.fb.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]],
  });

  /**
   * Submit del formulario de login
   */
  onSubmit(): void {
    if (this.loginForm.invalid) {
      this.markFormGroupTouched(this.loginForm);
      this.messageService.add({
        severity: 'warn',
        summary: 'Formulario Inválido',
        detail: 'Por favor, completa todos los campos correctamente.',
        life: 3000,
      });
      return;
    }

    this.isLoading.set(true);

    this.authService.login(this.loginForm.value).subscribe({
      next: (response) => {
        this.isLoading.set(false);

        this.messageService.add({
          severity: 'success',
          summary: 'Login Exitoso',
          detail: `Bienvenido, ${response.data.usuario.usuario_nombre}`,
          life: 2000,
        });

        // Dar tiempo para que el token se guarde en localStorage
        setTimeout(() => {
          this.redirectByRole(response.data.usuario.rol_id);
        }, 1000);
      },
      error: (error) => {
        this.isLoading.set(false);

        // Obtener mensaje de error del backend
        const errorMessage = this.getErrorMessage(error);

        this.messageService.add({
          severity: 'error',
          summary: 'Error de Autenticación',
          detail: errorMessage,
          life: 5000,
        });
      },
    });
  }

  /**
   * Redirigir según rol del usuario
   */
  private redirectByRole(rolId: number): void {
    switch (rolId) {
      case 1: // ADMIN
        this.router.navigate(['/admin/dashboard']);
        break;
      case 2: // PROJECT_MANAGER
        this.router.navigate(['/project-manager/dashboard']);
        break;
      case 3: // USER
        this.router.navigate(['/user/mis-tareas']);
        break;
      default:
        this.router.navigate(['/']);
    }
  }

  /**
   * Obtener mensaje de error del backend
   */
  private getErrorMessage(error: any): string {
    if (error.error?.mensajes && error.error.mensajes.length > 0) {
      return error.error.mensajes[0];
    }

    if (error.status === 0) {
      return 'Error de conexión. Verifica tu conexión a internet o que el servidor esté activo.';
    }

    return `Error ${error.status}: ${error.statusText || 'Error desconocido'}`;
  }

  /**
   * Marcar todos los campos del formulario como tocados (para mostrar errores)
   */
  private markFormGroupTouched(formGroup: FormGroup): void {
    Object.keys(formGroup.controls).forEach((key) => {
      const control = formGroup.get(key);
      control?.markAsTouched();

      if (control instanceof FormGroup) {
        this.markFormGroupTouched(control);
      }
    });
  }

  /**
   * Verificar si un campo tiene error
   */
  hasError(field: string, error: string): boolean {
    const control = this.loginForm.get(field);
    return !!(control?.hasError(error) && control?.touched);
  }

  /**
   * Toggle para mostrar/ocultar contraseña
   */
  togglePasswordVisibility(): void {
    this.showPassword.update((value) => !value);
  }
}
