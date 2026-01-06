import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DirectoryComponent } from './directory.component';
import { TeamSharedModule } from '../team/team-shared.module';

@NgModule({
  declarations: [DirectoryComponent],
  imports: [CommonModule, TeamSharedModule],
  exports: [DirectoryComponent],
})
export class DirectoryModule {}
