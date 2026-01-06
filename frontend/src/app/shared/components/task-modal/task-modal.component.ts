import { Component, EventEmitter, Input, OnInit, OnChanges, SimpleChanges, Output, signal, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { Task } from '../../../core/interfaces/task.interface';
import { MessageService } from 'primeng/api';
@Component({
  standalone: false,
  selector: 'app-task-modal',
  templateUrl: './task-modal.component.html',
  styleUrl: './task-modal.component.scss',
})
export class TaskModalComponent implements OnInit, OnChanges {
  @Input() visible = false;
  @Input() task: Task | null = null;
  @Input() proyectos: any[] = [];
  @Input() estados: any[] = [];
  @Input() prioridades: any[] = [];
  @Input() usuarios: any[] = [];
  @Input() userRolId: number = 1; 

  @Output() visibleChange = new EventEmitter<boolean>();
  @Output() onSave = new EventEmitter<any>();
  @Output() onCancel = new EventEmitter<void>();
  @Output() onDelete = new EventEmitter<number>();

  taskForm!: FormGroup;
  readonly isLoading = signal<boolean>(false);
  private readonly messageService = inject(MessageService);
  private formSubmitted = false;

  minDate: Date = new Date(new Date().setHours(0, 0, 0, 0));

  constructor(private fb: FormBuilder) { }

  ngOnInit(): void {
    this.initForm();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['task'] && this.taskForm) {
      this.updateForm();
    }
  }

  private initForm(): void {
    this.taskForm = this.fb.group({
      proyecto_id: [this.task?.proyecto_id || null, [Validators.required]],
      titulo: [this.task?.titulo || '', [Validators.required, Validators.minLength(3), Validators.maxLength(200)]],
      descripcion: [this.task?.descripcion || ''],
      estado_id: [this.task?.estado_id || 1],
      prioridad_id: [this.task?.prioridad_id || null, [Validators.required]],
      usuario_asignado: [this.task?.usuario_asignado_id || null],
      fecha_limite: [this.task?.fecha_limite ? new Date(this.task.fecha_limite) : null, [this.futureDateValidator]],
    });
  }

  private futureDateValidator(control: AbstractControl): ValidationErrors | null {
    if (!control.value) {
      return null;
    }

    const selectedDate = new Date(control.value);
    const now = new Date();

    selectedDate.setMilliseconds(0);
    now.setMilliseconds(0);

    if (selectedDate < now) {
      return { pastDate: true };
    }

    return null;
  }

  private updateForm(): void {
    this.taskForm.patchValue({
      proyecto_id: this.task?.proyecto_id || null,
      titulo: this.task?.titulo || '',
      descripcion: this.task?.descripcion || '',
      estado_id: this.task?.estado_id || 1,
      prioridad_id: this.task?.prioridad_id || null,
      usuario_asignado: this.task?.usuario_asignado_id || null,
      fecha_limite: this.task?.fecha_limite ? new Date(this.task.fecha_limite) : null,
    });

    this.taskForm.markAsUntouched();
    this.taskForm.markAsPristine();
  }

  get isEditMode(): boolean {
    return !!this.task;
  }

  get modalTitle(): string {
    if (this.isNormalUser && this.isEditMode) {
      return 'Actualizar Estado';
    }
    return this.isEditMode ? 'Editar Tarea' : 'Nueva Tarea';
  }

  /**
   * Verifica si el usuario actual es un usuario normal (rol 3)
   */
  get isNormalUser(): boolean {
    return this.userRolId === 3;
  }

  onSubmit(): void {
    this.formSubmitted = true;
    if (this.taskForm.invalid) {
      Object.keys(this.taskForm.controls).forEach((key) => {
        this.taskForm.get(key)?.markAsTouched();
      });

      const fechaControl = this.taskForm.get('fecha_limite');
      if (fechaControl?.errors?.['pastDate']) {
        this.messageService.add({
          severity: 'warn',
          summary: 'Fecha inválida',
          detail: 'La fecha de vencimiento no puede ser anterior a la fecha actual',
          life: 4000,
        });
      }

      return;
    }

    const formValue = this.taskForm.value;

    if (formValue.fecha_limite) {
      const fecha = new Date(formValue.fecha_limite);
      formValue.fecha_limite = this.formatDate(fecha);
    }

    this.onSave.emit({
      ...formValue,
      tarea_id: this.task?.id,
    });
  }

  private formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = date.getSeconds() === 0 ? String(new Date().getSeconds()).padStart(2, '0') : String(date.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
  }

  closeModal(): void {
    this.visibleChange.emit(false);
    this.taskForm.reset();
    this.formSubmitted = false;
    this.onCancel.emit();
  }

  handleDelete(): void {
    if (this.task) {
      this.onDelete.emit(this.task.id);
    }
  }

  getErrorMessage(fieldName: string): string {
    const control = this.taskForm.get(fieldName);

    if (!control || !control.errors) {
      return '';
    }

    if (!control.touched && !this.formSubmitted) {
      return '';
    }

    if (control.errors['required']) {
      return 'Este campo es requerido';
    }
    if (control.errors['minlength']) {
      return `Mínimo ${control.errors['minlength'].requiredLength} caracteres`;
    }
    if (control.errors['maxlength']) {
      return `Máximo ${control.errors['maxlength'].requiredLength} caracteres`;
    }
    if (control.errors['pastDate']) {
      return 'La fecha no puede ser anterior a la fecha actual';
    }

    return '';
  }
}
