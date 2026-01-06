import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { PrimeNgModule } from './prime-ng.module';
import { TaskCardComponent } from './components/task-card/task-card.component';
import { TaskModalComponent } from './components/task-modal/task-modal.component';
import { TaskBoardComponent } from './components/task-board/task-board.component';
import { TaskDetailModalComponent } from './components/task-detail-modal/task-detail-modal.component';
import { AdminLayoutComponent } from './layouts/admin-layout.component';
import { UserLayoutComponent } from './layouts/user-layout.component';

@NgModule({
  declarations: [TaskCardComponent, TaskModalComponent, TaskBoardComponent, TaskDetailModalComponent, AdminLayoutComponent, UserLayoutComponent],
  imports: [CommonModule, ReactiveFormsModule, RouterModule, PrimeNgModule],
  exports: [CommonModule, ReactiveFormsModule, PrimeNgModule, TaskCardComponent, TaskModalComponent, TaskBoardComponent, TaskDetailModalComponent, AdminLayoutComponent, UserLayoutComponent],
})
export class SharedModule { }
