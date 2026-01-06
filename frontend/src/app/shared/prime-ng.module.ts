import { NgModule } from '@angular/core';

// PrimeNG Modules
import { ButtonModule } from 'primeng/button';
import { InputTextModule } from 'primeng/inputtext';
import { PasswordModule } from 'primeng/password';
import { CardModule } from 'primeng/card';
import { ToastModule } from 'primeng/toast';
import { DrawerModule } from 'primeng/drawer';
import { MenubarModule } from 'primeng/menubar';
import { DialogModule } from 'primeng/dialog';
import { Select } from 'primeng/select';
import { DatePicker } from 'primeng/datepicker';
import { TextareaModule } from 'primeng/textarea';
import { ConfirmDialog } from 'primeng/confirmdialog';
import { TableModule } from 'primeng/table';
import { TooltipModule } from 'primeng/tooltip';
import { ToggleSwitchModule } from 'primeng/toggleswitch';

@NgModule({
  imports: [
    ButtonModule,
    InputTextModule,
    PasswordModule,
    CardModule,
    ToastModule,
    DrawerModule,
    MenubarModule,
    DialogModule,
    Select,
    DatePicker,
    TextareaModule,
    ConfirmDialog,
    TableModule,
    TooltipModule,
    ToggleSwitchModule,
  ],
  exports: [
    ButtonModule,
    InputTextModule,
    PasswordModule,
    CardModule,
    ToastModule,
    DrawerModule,
    MenubarModule,
    DialogModule,
    Select,
    DatePicker,
    TextareaModule,
    ConfirmDialog,
    TableModule,
    TooltipModule,
    ToggleSwitchModule,
  ],
})
export class PrimeNgModule {}