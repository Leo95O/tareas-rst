import { Component, EventEmitter, Input, OnInit, OnChanges, SimpleChanges, Output, signal, inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Project, Sucursal } from '../../../core/interfaces/project.interface';
import { ProjectService } from '../../../core/services/project.service';

/**
 * ProjectModalComponent - Modal reutilizable para crear/editar proyectos
 */
@Component({
  standalone: false,
  selector: 'app-project-modal',
  templateUrl: './project-modal.component.html',
  styleUrl: './project-modal.component.scss',
})
export class ProjectModalComponent implements OnInit, OnChanges {
  @Input() visible = false;
  @Input() project: Project | null = null;

  @Output() visibleChange = new EventEmitter<boolean>();
  @Output() onSave = new EventEmitter<any>();
  @Output() onCancel = new EventEmitter<void>();

  private readonly projectService = inject(ProjectService);

  projectForm!: FormGroup;
  readonly isLoading = signal<boolean>(false);
  readonly sucursales = this.projectService.sucursales;

  constructor(private fb: FormBuilder) { }

  ngOnInit(): void {
    this.initForm();
    this.projectService.getSucursales();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['project'] && this.projectForm) {
      this.updateForm();
    }
    if (changes['visible'] && this.visible) {
      // Recargar sucursales al abrir el modal
      this.projectService.getSucursales();
    }
  }

  private initForm(): void {
    this.projectForm = this.fb.group({
      nombre: [
        this.project?.nombre || '',
        [Validators.required, Validators.minLength(3), Validators.maxLength(100)],
      ],
      descripcion: [
        this.project?.descripcion || '',
        [Validators.maxLength(500)],
      ],
      sucursal_id: [
        this.project?.sucursal_id || null,
        [Validators.required],
      ],
      fecha_inicio: [
        this.project?.fecha_inicio || null,
      ],
      fecha_fin: [
        this.project?.fecha_fin || null,
      ],
    });
  }

  private updateForm(): void {
    this.projectForm.patchValue({
      nombre: this.project?.nombre || '',
      descripcion: this.project?.descripcion || '',
      sucursal_id: this.project?.sucursal_id || null,
      fecha_inicio: this.project?.fecha_inicio || null,
      fecha_fin: this.project?.fecha_fin || null,
    });

    this.projectForm.markAsUntouched();
    this.projectForm.markAsPristine();
  }

  get isEditMode(): boolean {
    return !!this.project;
  }

  get modalTitle(): string {
    return this.isEditMode ? 'Editar Proyecto' : 'Nuevo Proyecto';
  }

  onSubmit(): void {
    if (this.projectForm.invalid) {
      Object.keys(this.projectForm.controls).forEach((key) => {
        this.projectForm.get(key)?.markAsTouched();
      });
      return;
    }

    const formValue = this.projectForm.value;
    this.onSave.emit({
      ...formValue,
      proyecto_id: this.project?.id,
    });
  }

  closeModal(): void {
    this.visibleChange.emit(false);
    this.projectForm.reset();
    this.onCancel.emit();
  }

  getErrorMessage(fieldName: string): string {
    const control = this.projectForm.get(fieldName);

    if (!control || !control.touched || !control.errors) {
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

    return '';
  }
}
