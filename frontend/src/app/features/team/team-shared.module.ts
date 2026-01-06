import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SharedModule } from '../../shared/shared-module';
import { TeamComponent } from './team.component';
import { UserModalComponent } from './user-modal/user-modal.component';
import { ConfirmationService, MessageService } from 'primeng/api';

/**
 * TeamSharedModule - Módulo compartido para reutilizar TeamComponent
 * Sin routing, solo el componente y sus dependencias
 */
@NgModule({
  declarations: [TeamComponent, UserModalComponent],
  imports: [CommonModule, SharedModule],
  providers: [MessageService, ConfirmationService],
  exports: [TeamComponent],
})
export class TeamSharedModule {}
