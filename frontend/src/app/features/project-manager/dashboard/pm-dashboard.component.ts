import { Component, inject, OnInit, signal, computed } from '@angular/core';
import { MessageService } from 'primeng/api';
import { DashboardService } from '../../../core/services/dashboard.service';
import { DashboardReport } from '../../../core/interfaces/report.interface';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  standalone: false,
  selector: 'app-pm-dashboard',
  templateUrl: './pm-dashboard.component.html',
  styleUrl: './pm-dashboard.component.scss',
})
export class PMDashboardComponent implements OnInit {
  private readonly dashboardService = inject(DashboardService);
  private readonly authService = inject(AuthService);
  private readonly messageService = inject(MessageService);

  // Signals
  readonly currentUser = this.authService.currentUser;
  readonly dashboardReport = signal<DashboardReport | null>(null);
  readonly isLoading = signal<boolean>(false);

  // Computed signals mapeados del reporte backend
  readonly proyectosActivos = computed(() => this.dashboardReport()?.resumen.total_proyectos ?? 0);
  readonly tareasVencidasEquipo = computed(() => this.dashboardReport()?.lista_vencidas.length ?? 0);
  readonly totalTareasEquipo = computed(() => this.dashboardReport()?.resumen.total_tareas ?? 0);
  readonly tareasCompletadasEquipo = computed(() => {
    const report = this.dashboardReport();
    if (!report) return 0;
    const completadasState = report.grafico_estados.find(e => e.estado_nombre.toLowerCase() === 'completada');
    return completadasState ? completadasState.cantidad : 0;
  });

  // Proyectos con progreso
  readonly proyectosConProgreso = computed(() => {
    return this.dashboardReport()?.tabla_proyectos.map(p => ({
      proyecto_nombre: p.proyecto_nombre,
      totalTareas: p.total_tareas,
      tareasCompletadas: p.completadas,
      progreso: p.porcentaje
    })) ?? [];
  });

  // Tareas vencidas
  readonly listaVencidas = computed(() => this.dashboardReport()?.lista_vencidas ?? []);

  // Carga de trabajo por usuario 
  readonly cargaTrabajoPorUsuario = computed(() => {
    return this.dashboardReport()?.carga_trabajo.map(u => ({
      nombre: u.usuario_nombre,
      count: u.total_tareas,
      completadas: u.completadas
    })) ?? [];
  });

  ngOnInit(): void {
    this.loadData();
  }

  private loadData(): void {
    this.isLoading.set(true);
    this.dashboardService.getDashboardData().subscribe({
      next: (response) => {
        if (response.tipo === 1) {
          this.dashboardReport.set(response.data);
        }
        this.isLoading.set(false);
      },
      error: (err) => {
        this.messageService.add({
          severity: 'error',
          summary: 'Error',
          detail: err.error?.mensajes?.[0] || 'No se pudieron cargar los datos del dashboard'
        });
        this.isLoading.set(false);
      }
    });
  }

  refresh(): void {
    this.loadData();
  }

  getProgressBarColor(progreso: number): string {
    if (progreso >= 75) return 'bg-green-500';
    if (progreso >= 50) return 'bg-blue-500';
    if (progreso >= 25) return 'bg-yellow-500';
    return 'bg-red-500';
  }

  getCargaPercentage(count: number): number {
    const total = this.totalTareasEquipo();
    if (total === 0) return 0;
    return Math.round((count / total) * 100);
  }
}
