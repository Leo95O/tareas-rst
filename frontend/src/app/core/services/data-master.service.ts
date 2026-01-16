import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DataMasterService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = environment.apiUrl;

  // --- SIGNALS: Almacenes reactivos de datos ---
  // Aquí guardamos las listas una vez descargadas
  readonly roles = signal<any[]>([]);
  readonly estadosUsuario = signal<any[]>([]);
  readonly estadosSucursal = signal<any[]>([]);
  readonly estadosProyecto = signal<any[]>([]);
  readonly estadosTarea = signal<any[]>([]);
    readonly prioridades = signal<any[]>([]);

  // Bandera para saber si ya cargamos los datos (evita recargas innecesarias)
  private dataLoaded = false;

  constructor() {}

  /**
   * Carga todos los catálogos del sistema en UNA sola petición.
   * Si ya están cargados, no hace nada (cache simple).
   */
  loadAll(): Observable<ApiResponse<any>> {
    // Si ya tenemos datos, podríamos retornar un observable vacío o directo,
    // pero por seguridad dejaremos que se pueda forzar la recarga.
    
    return this.http.get<ApiResponse<any>>(`${this.apiUrl}/datamaster/catalogos`).pipe(
      tap(response => {
        if (response.tipo === 1 && response.data) {
          const d = response.data;

          // Llenamos los Signals con la respuesta del Backend
          this.roles.set(d.roles || []);
          this.estadosUsuario.set(d.estados_usuario || []);
          this.estadosSucursal.set(d.estados_sucursal || []);
          this.estadosProyecto.set(d.estados_proyecto || []);
          this.estadosTarea.set(d.estados_tarea || []);
          this.prioridades.set(d.prioridades || []);

          this.dataLoaded = true;
          console.log('📦 DataMaster: Catálogos cargados correctamente', d);
        }
      })
    );
  }

  /**
   * Helper para limpiar datos al cerrar sesión
   */
  clear(): void {
    this.roles.set([]);
    this.estadosUsuario.set([]);
    this.estadosSucursal.set([]);
    this.estadosProyecto.set([]);
    this.estadosTarea.set([]);
    this.dataLoaded = false;
  }
}