import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ProjectsRoutingModule } from './projects-routing.module';
import { SharedModule } from '../../shared/shared-module';
import { ConfirmationService, MessageService } from 'primeng/api';

// Components
import { ProjectsComponent } from './projects.component';
import { ProjectModalComponent } from './project-modal/project-modal.component';
import { ProjectDetailModalComponent } from './project-detail-modal/project-detail-modal.component';

@NgModule({
  declarations: [ProjectsComponent, ProjectModalComponent, ProjectDetailModalComponent],
  imports: [CommonModule, ProjectsRoutingModule, SharedModule],
  providers: [MessageService, ConfirmationService],
})
export class ProjectsModule { }
