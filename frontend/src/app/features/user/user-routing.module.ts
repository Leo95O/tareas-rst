import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { UserLayoutComponent } from '../../shared/layouts/user-layout.component';
import { TaskPoolComponent } from '../my-tasks/task-pool/task-pool.component';

const routes: Routes = [
    {
        path: '',
        component: UserLayoutComponent,
        children: [
            {
                path: '',
                redirectTo: 'mis-tareas',
                pathMatch: 'full',
            },
            {
                path: 'mis-tareas',
                loadChildren: () => import('../my-tasks/my-tasks.module').then(m => m.MyTasksModule),
            },
            {
                path: 'bolsa-tareas',
                component: TaskPoolComponent,
            },
        ],
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule],
})
export class UserRoutingModule { }
