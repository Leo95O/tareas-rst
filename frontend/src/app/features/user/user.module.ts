import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { UserRoutingModule } from './user-routing.module';
import { SharedModule } from '../../shared/shared-module';
import { ConfirmationService, MessageService } from 'primeng/api';

// Components
import { TaskPoolComponent } from '../my-tasks/task-pool/task-pool.component';

@NgModule({
    declarations: [TaskPoolComponent],
    imports: [
        CommonModule,
        UserRoutingModule,
        SharedModule,
    ],
    providers: [MessageService, ConfirmationService],
})
export class UserModule { }
