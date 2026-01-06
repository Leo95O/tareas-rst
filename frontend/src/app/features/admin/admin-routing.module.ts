import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminLayoutComponent } from '../../shared/layouts/admin-layout.component';
import { DashboardComponent } from './dashboard/dashboard.component';

const routes: Routes = [
  {
    path: '',
    component: AdminLayoutComponent,
    children: [
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full',
      },
      {
        path: 'dashboard',
        component: DashboardComponent,
      },
      {
        path: 'mis-tareas',
        loadChildren: () => import('../my-tasks/my-tasks.module').then(m => m.MyTasksModule),
      },
      {
        path: 'proyectos',
        loadChildren: () => import('../projects/projects.module').then(m => m.ProjectsModule),
      },
      {
        path: 'equipo',
        loadChildren: () => import('../team/team.module').then(m => m.TeamModule),
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AdminRoutingModule { }
