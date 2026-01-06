import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges, inject, signal } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { User, CreateUserDto, UpdateUserDto, Role } from '../../../core/interfaces/user.interface';

/**
 * UserModalComponent - Modal para crear/editar usuarios
 *
 * Features:
 * - Formulario reactivo con validaciones
 * - Modo creación (password requerido)
 * - Modo edición (password opcional)
 * - Pre-llenado automático con ngOnChanges
 * - Dropdown de roles
 * - Toggle de estado activo
 */
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
    // Detectar cambios en @Input user para pre-llenar formulario
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
      password: ['', [Validators.minLength(6)]],
      rol_id: [null, Validators.required],
      usuario_estado: [true], // Boolean for toggle switch
    });

    // Validación condicional: password requerido solo en modo creación
    this.userForm.get('password')?.valueChanges.subscribe(() => {
      this.updatePasswordValidation();
    });
  }

  /**
   * Actualizar validación de password según modo
   */
  private updatePasswordValidation(): void {
    const passwordControl = this.userForm.get('password');
    const nombreControl = this.userForm.get('usuario_nombre');

    if (!this.isEditMode) {
      // Modo creación: password y nombre requeridos
      passwordControl?.setValidators([Validators.required, Validators.minLength(6)]);
      nombreControl?.setValidators([Validators.required, Validators.minLength(3), Validators.maxLength(50)]);
    } else {
      // Modo edición: password y nombre opcionales
      passwordControl?.setValidators([Validators.minLength(6)]);
      nombreControl?.clearValidators();
    }

    passwordControl?.updateValueAndValidity({ emitEvent: false });
    nombreControl?.updateValueAndValidity({ emitEvent: false });
  }

  /**
   * Actualizar formulario con datos del usuario (pre-llenado)
   */
  private updateForm(): void {
    if (this.user) {
      this.userForm.patchValue({
        usuario_nombre: this.user.usuario_nombre,
        usuario_correo: this.user.usuario_correo,
        password: '', // Password vacío en edición
        rol_id: this.user.rol_id,
        usuario_estado: this.user.usuario_estado === 1, // Convert number to boolean
      });
      this.userForm.markAsUntouched();
      this.userForm.markAsPristine();
    } else {
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
   * Manejar submit del formulario
   */
  onSubmit(): void {
    this.submitted.set(true);

    if (this.userForm.invalid) {
      // Marcar todos los campos como touched para mostrar errores
      Object.keys(this.userForm.controls).forEach(key => {
        this.userForm.get(key)?.markAsTouched();
      });
      return;
    }

    const formValue = this.userForm.value;

    if (this.isEditMode) {
      // Modo edición: enviar solo campos modificados
      const updateData: UpdateUserDto = {};

      if (formValue.usuario_nombre !== this.user?.usuario_nombre) {
        updateData.usuario_nombre = formValue.usuario_nombre;
      }

      if (formValue.usuario_correo !== this.user?.usuario_correo) {
        updateData.usuario_correo = formValue.usuario_correo;
      }

      if (formValue.password && formValue.password.trim() !== '') {
        updateData.password = formValue.password;
      }

      if (formValue.rol_id !== this.user?.rol_id) {
        updateData.rol_id = formValue.rol_id;
      }

      // Convert boolean to number for backend
      const newState = formValue.usuario_estado ? 1 : 0;
      if (newState !== this.user?.usuario_estado) {
        updateData.usuario_estado = newState;
      }

      // Siempre emitir, incluso si no hay cambios (el backend validará)
      this.save.emit(updateData);
    } else {
      // Modo creación: enviar todos los campos requeridos
      const createData: CreateUserDto = {
        usuario_nombre: formValue.usuario_nombre,
        usuario_correo: formValue.usuario_correo,
        password: formValue.password,
        rol_id: formValue.rol_id,
      };

      this.save.emit(createData);
    }
  }

  /**
   * Manejar cancelación
   */
  onCancel(): void {
    this.userForm.reset();
    this.submitted.set(false);
    this.cancel.emit();
  }

  /**
   * Cerrar modal (overlay click)
   */
  onHide(): void {
    this.onCancel();
  }

  /**
   * Verificar si un campo tiene error
   */
  hasError(fieldName: string, errorType: string): boolean {
    const control = this.userForm.get(fieldName);
    return !!(control?.hasError(errorType) && (control?.touched || this.submitted()));
  }

  /**
   * Obtener mensaje de error
   */
  getErrorMessage(fieldName: string): string {
    const control = this.userForm.get(fieldName);

    if (!control || !control.errors) return '';

    if (control.hasError('required')) {
      return 'Este campo es requerido';
    }

    if (control.hasError('email')) {
      return 'Email inválido';
    }

    if (control.hasError('minlength')) {
      const minLength = control.errors['minlength'].requiredLength;
      return `Mínimo ${minLength} caracteres`;
    }

    if (control.hasError('maxlength')) {
      const maxLength = control.errors['maxlength'].requiredLength;
      return `Máximo ${maxLength} caracteres`;
    }

    return '';
  }

  /**
   * Getters
   */
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
