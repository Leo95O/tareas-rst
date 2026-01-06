import { Component, inject, signal } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MessageService } from 'primeng/api';
import { AuthService } from '../../../core/services/auth.service';

@Component({
    standalone: false,
    selector: 'app-register',
    templateUrl: './register.component.html',
    providers: [MessageService]
})
export class RegisterComponent {
    private readonly fb = inject(FormBuilder);
    private readonly authService = inject(AuthService);
    private readonly router = inject(Router);
    private readonly messageService = inject(MessageService);

    readonly isLoading = signal<boolean>(false);

    registerForm: FormGroup = this.fb.group({
        usuario_nombre: ['', [Validators.required, Validators.minLength(3)]],
        usuario_correo: ['', [Validators.required, Validators.email]],
        password: ['', [Validators.required, Validators.minLength(6)]]
    });

    onSubmit(): void {
        if (this.registerForm.invalid) {
            this.registerForm.markAllAsTouched();
            return;
        }

        this.isLoading.set(true);

        const registerData = {
            usuario_nombre: this.registerForm.value.usuario_nombre,
            usuario_correo: this.registerForm.value.usuario_correo,
            usuario_password: this.registerForm.value.password
        };

        this.authService.register({
            nombre: this.registerForm.value.usuario_nombre,
            email: this.registerForm.value.usuario_correo,
            password: this.registerForm.value.password
        }).subscribe({
            next: (response) => {
                this.isLoading.set(false);
                this.messageService.add({
                    severity: 'success',
                    summary: 'Registro Exitoso',
                    detail: 'Tu cuenta ha sido creada. Por favor inicia sesión.',
                    life: 3000
                });
                setTimeout(() => this.router.navigate(['/login']), 1500);
            },
            error: (error) => {
                this.isLoading.set(false);
                this.messageService.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: error.error?.mensajes?.[0] || 'Error al registrar usuario'
                });
            }
        });
    }

    hasError(field: string, error: string): boolean {
        const control = this.registerForm.get(field);
        return !!(control?.hasError(error) && control?.touched);
    }
}
