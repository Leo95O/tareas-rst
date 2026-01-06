import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AdminRoutingModule } from './admin-routing.module';
import { SharedModule } from '../../shared/shared-module';
import { ConfirmationService, MessageService } from 'primeng/api';

// Components
import { DashboardComponent } from './dashboard/dashboard.component';

@NgModule({
  declarations: [DashboardComponent],
  imports: [CommonModule, AdminRoutingModule, SharedModule],
  providers: [MessageService, ConfirmationService],
})
export class AdminModule {}
