import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges, inject, signal } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { User, CreateUserDto, UpdateUserDto, Role } from '../../../core/interfaces/user.interface';

@Component({
  standalone: false,
  selector: 'app-user-modal',
  templateUrl: './user-modal.component.html',
  styleUrl: './user-modal.component.scss',
})
export class UserModalComponent implements OnInit, OnChanges {
  private readonly fb = inject(FormBuilder);

  @Input() visible: boolean = false;
  @Input() user: User | null = null;
  @Input() roles: Role[] = [];
  @Output() save = new EventEmitter<CreateUserDto | UpdateUserDto>();
  @Output() cancel = new EventEmitter<void>();

  userForm!: FormGroup;
  readonly submitted = signal<boolean>(false);

  ngOnInit(): void {
    this.initForm();
  }

  ngOnChanges(changes: SimpleChanges): void {
    // Si cambia el usuario seleccionado (o se pone null), actualizamos el form
    if (changes['user'] && this.userForm) {
      this.updateForm();
    }
  }

  /**
   * Inicializar formulario
   */
  private initForm(): void {
    this.userForm = this.fb.group({
      usuario_nombre: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50)]],
      usuario_correo: ['', [Validators.required, Validators.email]],
      // Usamos 'password' en el form del front, aunque el backend espere 'usuario_password'
      password: [''], 
      rol_id: [null, Validators.required],
      usuario_estado: [true], // El ToggleSwitch trabaja con booleans
    });
  }

  /**
   * Actualizar validación de password según si es Crear o Editar
   */
  private updatePasswordValidation(): void {
    const passwordControl = this.userForm.get('password');
    const nombreControl = this.userForm.get('usuario_nombre');

    if (!this.isEditMode) {
      // MODO CREACIÓN: Password y Nombre obligatorios
      passwordControl?.setValidators([Validators.required, Validators.minLength(6)]);
      nombreControl?.setValidators([Validators.required, Validators.minLength(3), Validators.maxLength(50)]);
    } else {
      // MODO EDICIÓN: Password opcional (solo si quiere cambiarla)
      passwordControl?.setValidators([Validators.minLength(6)]);
      // El nombre sigue validándose por su cuenta si se toca
    }

    // Actualizamos el estado de los controles sin emitir eventos para evitar bucles
    passwordControl?.updateValueAndValidity({ emitEvent: false });
    nombreControl?.updateValueAndValidity({ emitEvent: false });
  }

  /**
   * Rellenar o limpiar el formulario
   */
  private updateForm(): void {
    if (this.user) {
      // --- CARGAR DATOS (EDICIÓN) ---
      this.userForm.patchValue({
        usuario_nombre: this.user.usuario_nombre,
        usuario_correo: this.user.usuario_correo,
        password: '', // Siempre limpia la contraseña al editar
        rol_id: this.user.rol_id,
        usuario_estado: this.user.usuario_estado === 1, // Convertimos 1/0 a true/false
      });
    } else {
      // --- LIMPIAR (CREACIÓN) ---
      this.userForm.reset({
        usuario_nombre: '',
        usuario_correo: '',
        password: '',
        rol_id: null,
        usuario_estado: true,
      });
    }

    this.submitted.set(false);
    this.updatePasswordValidation();
  }

  /**
   * Enviar datos al padre (TeamComponent)
   */
  onSubmit(): void {
    this.submitted.set(true);

    if (this.userForm.invalid) {
      this.userForm.markAllAsTouched(); // Muestra los errores rojos
      return;
    }

    const formValue = this.userForm.value;

    if (this.isEditMode) {
      // --- PREPARAR DATOS PARA ACTUALIZAR ---
      const updateData: UpdateUserDto = {};

      // Solo enviamos lo que cambió para ahorrar datos
      if (formValue.usuario_nombre !== this.user?.usuario_nombre) {
        updateData.usuario_nombre = formValue.usuario_nombre;
      }
      if (formValue.usuario_correo !== this.user?.usuario_correo) {
        updateData.usuario_correo = formValue.usuario_correo;
      }
      // Solo enviamos password si el usuario escribió algo
      if (formValue.password && formValue.password.trim() !== '') {
        updateData.usuario_password = formValue.password;
      }
      if (formValue.rol_id !== this.user?.rol_id) {
        updateData.rol_id = formValue.rol_id;
      }
      
      // Convertir boolean (true/false) a número (1/0) para PHP
      const newState = formValue.usuario_estado ? 1 : 0;
      if (newState !== this.user?.usuario_estado) {
        updateData.usuario_estado = newState;
      }

      this.save.emit(updateData);
    } else {
      // --- PREPARAR DATOS PARA CREAR ---
      const createData: CreateUserDto = {
        usuario_nombre: formValue.usuario_nombre,
        usuario_correo: formValue.usuario_correo,
        usuario_password: formValue.password, // Aquí mapeamos 'password' -> 'usuario_password'
        rol_id: formValue.rol_id,
      };

      this.save.emit(createData);
    }
  }

  onCancel(): void {
    this.userForm.reset();
    this.submitted.set(false);
    this.cancel.emit();
  }

  // Se llama cuando cierras el modal con la X o click fuera
  onHide(): void {
    this.onCancel();
  }

  // Helper para el HTML: ¿Hay error visual?
  hasError(fieldName: string, errorType: string): boolean {
    const control = this.userForm.get(fieldName);
    return !!(control?.hasError(errorType) && (control?.touched || this.submitted()));
  }

  // Helper para mensajes de error
  getErrorMessage(fieldName: string): string {
    const control = this.userForm.get(fieldName);
    if (!control || !control.errors) return '';

    if (control.hasError('required')) return 'Este campo es requerido';
    if (control.hasError('email')) return 'Email inválido';
    if (control.hasError('minlength')) {
      return `Mínimo ${control.errors['minlength'].requiredLength} caracteres`;
    }
    if (control.hasError('maxlength')) {
      return `Máximo ${control.errors['maxlength'].requiredLength} caracteres`;
    }

    return '';
  }

  get isEditMode(): boolean {
    return !!this.user;
  }

  get modalTitle(): string {
    return this.isEditMode ? 'Editar Usuario' : 'Nuevo Usuario';
  }

  get saveButtonLabel(): string {
    return this.isEditMode ? 'Actualizar' : 'Crear Usuario';
  }
}