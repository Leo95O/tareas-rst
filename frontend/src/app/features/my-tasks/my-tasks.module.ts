import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MyTasksRoutingModule } from './my-tasks-routing.module';
import { SharedModule } from '../../shared/shared-module';
import { ConfirmationService, MessageService } from 'primeng/api';

// Components
import { MyTasksComponent } from './my-tasks.component';

@NgModule({
  declarations: [MyTasksComponent],
  imports: [CommonModule, MyTasksRoutingModule, SharedModule],
  providers: [MessageService, ConfirmationService],
})
export class MyTasksModule { }
