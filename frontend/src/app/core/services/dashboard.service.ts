import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../interfaces/api-response.interface';
import { DashboardReport } from '../interfaces/report.interface';

@Injectable({
    providedIn: 'root'
})
export class DashboardService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    getDashboardData(): Observable<ApiResponse<DashboardReport>> {
        return this.http.get<ApiResponse<DashboardReport>>(`${this.apiUrl}/reportes/dashboard`);
    }
}
