import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { authGuard, noAuthGuard, roleGuard } from './core/guards/auth.guard';
import { LoginComponent } from './features/auth/login/login.component';
import { RegisterComponent } from './features/auth/register/register.component';

const routes: Routes = [
  {
    path: '',
    redirectTo: '/login',
    pathMatch: 'full',
  },
  {
    path: 'login',
    component: LoginComponent,
    canActivate: [noAuthGuard],
  },
  {
    path: 'register',
    component: RegisterComponent,
    canActivate: [noAuthGuard],
  },
  {
    path: 'admin',
    loadChildren: () =>
      import('./features/admin/admin.module').then((m) => m.AdminModule),
    canActivate: [authGuard, roleGuard(['ADMIN'])],
  },
  {
    path: 'project-manager',
    loadChildren: () =>
      import('./features/project-manager/project-manager.module').then((m) => m.ProjectManagerModule),
    canActivate: [authGuard, roleGuard(['PROJECT_MANAGER'])],
  },
  {
    path: 'user',
    loadChildren: () =>
      import('./features/user/user.module').then((m) => m.UserModule),
    canActivate: [authGuard, roleGuard(['USER'])],
  },
  // Wildcard route - captura cualquier ruta no definida
  {
    path: '**',
    redirectTo: '/login',
    pathMatch: 'full',
  },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule { }
