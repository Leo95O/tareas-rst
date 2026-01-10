import { NgModule } from '@angular/core';

// Componentes Básicos
import { ButtonModule } from 'primeng/button';
import { InputTextModule } from 'primeng/inputtext';
import { PasswordModule } from 'primeng/password';
import { CardModule } from 'primeng/card';
import { ToastModule } from 'primeng/toast';
import { MenubarModule } from 'primeng/menubar';
import { DialogModule } from 'primeng/dialog';
import { TextareaModule } from 'primeng/textarea';
import { TableModule } from 'primeng/table';
import { TooltipModule } from 'primeng/tooltip';
import { ConfirmDialogModule } from 'primeng/confirmdialog'; // Ojo: Module
import { DrawerModule } from 'primeng/drawer';

// --- COMPONENTES MODERNOS (PrimeNG v18/v20) ---
import { SelectModule } from 'primeng/select';           // Reemplaza a Dropdown
import { ToggleSwitchModule } from 'primeng/toggleswitch'; // Reemplaza a InputSwitch
import { DatePickerModule } from 'primeng/datepicker';   // Reemplaza a Calendar

@NgModule({
  imports: [
    ButtonModule,
    InputTextModule,
    PasswordModule,
    CardModule,
    ToastModule,
    MenubarModule,
    DialogModule,
    TextareaModule,
    TableModule,
    TooltipModule,
    ConfirmDialogModule,
    DrawerModule,
    // Nuevos:
    SelectModule,
    ToggleSwitchModule,
    DatePickerModule
  ],
  exports: [
    ButtonModule,
    InputTextModule,
    PasswordModule,
    CardModule,
    ToastModule,
    MenubarModule,
    DialogModule,
    TextareaModule,
    TableModule,
    TooltipModule,
    ConfirmDialogModule,
    DrawerModule,
    // Exportamos los nuevos:
    SelectModule,
    ToggleSwitchModule,
    DatePickerModule
  ],
})
export class PrimeNgModule {}