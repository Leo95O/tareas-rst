import { NgModule, provideBrowserGlobalErrorListeners, APP_INITIALIZER } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing-module';
import { App } from './app';
import { CoreModule } from './core/core-module';
import { SharedModule } from './shared/shared-module';
import { FeaturesModule } from './features/features-module';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { providePrimeNG } from 'primeng/config';
import Aura from '@primeuix/themes/aura';
import { AuthService } from './core/services/auth.service';
import { MessageService } from 'primeng/api';

/**
 * Factory para inicializar autenticación antes de que la app arranque
 */
export function initializeApp(authService: AuthService) {
  return () => authService.initializeAuth();
}

@NgModule({
  declarations: [App],
  imports: [
    BrowserModule,
    AppRoutingModule,
    CoreModule,
    SharedModule,
    FeaturesModule,
  ],
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideAnimationsAsync(),
    providePrimeNG({
      theme: {
        preset: Aura,
      },
    }),
    MessageService, // Proveedor global para toasts
    {
      provide: APP_INITIALIZER,
      useFactory: initializeApp,
      deps: [AuthService],
      multi: true,
    },
  ],
  bootstrap: [App],
})
export class AppModule {}
