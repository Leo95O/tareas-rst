export interface ApiResponse<T> {
    tipo: number;
    mensajes: string[];
    data: T;
}
