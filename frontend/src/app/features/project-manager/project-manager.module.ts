import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ProjectManagerRoutingModule } from './project-manager-routing.module';
import { SharedModule } from '../../shared/shared-module';
import { MyTasksModule } from '../my-tasks/my-tasks.module';
import { ProjectsModule } from '../projects/projects.module';
import { DirectoryModule } from '../directory/directory.module';
import { MessageService } from 'primeng/api';

// Components
import { PMDashboardComponent } from './dashboard/pm-dashboard.component';

@NgModule({
  declarations: [PMDashboardComponent],
  imports: [
    CommonModule,
    ProjectManagerRoutingModule,
    SharedModule,
    MyTasksModule,
    ProjectsModule,
    DirectoryModule,
  ],
  providers: [MessageService],
})
export class ProjectManagerModule {}
