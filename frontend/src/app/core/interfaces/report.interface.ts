export interface DashboardReport {
    resumen: {
        total_usuarios: number;
        total_proyectos: number;
        total_tareas: number;
    };
    grafico_estados: {
        estado_nombre: string;
        cantidad: number;
    }[];
    tabla_proyectos: {
        proyecto_nombre: string;
        total_tareas: number;
        completadas: number;
        porcentaje: number;
    }[];
    lista_vencidas: {
        tarea_titulo: string;
        fecha_limite: string;
        usuario_nombre: string;
        proyecto_nombre: string;
    }[];
    carga_trabajo: {
        usuario_nombre: string;
        total_tareas: number;
        completadas: number;
    }[];
}
