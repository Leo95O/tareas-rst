import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminLayoutComponent } from '../../shared/layouts/admin-layout.component';
import { PMDashboardComponent } from './dashboard/pm-dashboard.component';
import { MyTasksComponent } from '../my-tasks/my-tasks.component';
import { ProjectsComponent } from '../projects/projects.component';
import { TaskBoardComponent } from '../../shared/components/task-board/task-board.component';
import { DirectoryComponent } from '../directory/directory.component';

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
        component: PMDashboardComponent,
      },
      {
        path: 'mis-tareas',
        component: MyTasksComponent,
      },
      {
        path: 'proyectos',
        component: ProjectsComponent,
      },
      {
        path: 'tablero-equipo',
        component: TaskBoardComponent,
      },
      {
        path: 'directorio',
        component: DirectoryComponent,
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ProjectManagerRoutingModule {}
