<?php

use DI\ContainerBuilder;
use App\Config\Database;

// 1. IMPORTAR INTERFACES
use App\Interfaces\DataMaster\DataMasterRepositoryInterface;
use App\Interfaces\LoginGuard\LoginGuardRepositoryInterface;
use App\Interfaces\Proyecto\ProyectoRepositoryInterface;
use App\Interfaces\Reporte\ReporteRepositoryInterface;
use App\Interfaces\Tarea\TareaRepositoryInterface;
use App\Interfaces\Usuario\UsuarioRepositoryInterface;
use App\Interfaces\Sucursal\SucursalRepositoryInterface; // <--- NUEVO
use App\Interfaces\Sucursal\SucursalServiceInterface;    // <--- NUEVO

use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Interfaces\Tarea\TareaServiceInterface;
use App\Interfaces\Usuario\UsuarioServiceInterface;

// 2. IMPORTAR CLASES CONCRETAS
use App\Repositories\DataMasterRepository;
use App\Repositories\LoginGuardRepository;
use App\Repositories\ProyectoRepository;
use App\Repositories\ReporteRepository;
use App\Repositories\TareaRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\SucursalRepository; // <--- NUEVO

use App\Services\LoginGuardService;
use App\Services\ProyectoService;
use App\Services\TareaService;
use App\Services\UsuarioService;
use App\Services\SucursalService;       // <--- NUEVO

// 3. IMPORTAR CONTROLADORES
use App\Controllers\DataMasterController;
use App\Controllers\ProyectoController;
use App\Controllers\ReporteController;
use App\Controllers\TareaController;
use App\Controllers\UsuarioController;
use App\Controllers\SucursalController; // <--- NUEVO

$builder = new ContainerBuilder();

$builder->addDefinitions([

    // --- A. Base de Datos ---
    PDO::class => function () {
        return Database::getInstance()->getConnection();
    },

    // --- B. Repositorios (Data Layer) ---
    DataMasterRepositoryInterface::class => \DI\create(DataMasterRepository::class)->constructor(\DI\get(PDO::class)),
    LoginGuardRepositoryInterface::class => \DI\create(LoginGuardRepository::class)->constructor(\DI\get(PDO::class)),
    ProyectoRepositoryInterface::class   => \DI\create(ProyectoRepository::class)->constructor(\DI\get(PDO::class)),
    ReporteRepositoryInterface::class    => \DI\create(ReporteRepository::class)->constructor(\DI\get(PDO::class)),
    TareaRepositoryInterface::class      => \DI\create(TareaRepository::class)->constructor(\DI\get(PDO::class)),
    UsuarioRepositoryInterface::class    => \DI\create(UsuarioRepository::class)->constructor(\DI\get(PDO::class)),
    
    // NUEVO: Repositorio de Sucursales
    SucursalRepositoryInterface::class   => \DI\create(SucursalRepository::class)->constructor(\DI\get(PDO::class)),

    // --- C. Servicios (Business Layer) ---
    LoginGuardServiceInterface::class => \DI\create(LoginGuardService::class)
        ->constructor(\DI\get(LoginGuardRepositoryInterface::class)),

    ProyectoServiceInterface::class => \DI\create(ProyectoService::class)
        ->constructor(\DI\get(ProyectoRepositoryInterface::class)),

    TareaServiceInterface::class => \DI\create(TareaService::class)
        ->constructor(\DI\get(TareaRepositoryInterface::class)),

    UsuarioServiceInterface::class => \DI\create(UsuarioService::class)
        ->constructor(
            \DI\get(UsuarioRepositoryInterface::class),
            \DI\get(LoginGuardServiceInterface::class)
        ),

    // NUEVO: Servicio de Sucursales (Recibe su Repo)
    SucursalServiceInterface::class => \DI\create(SucursalService::class)
        ->constructor(\DI\get(SucursalRepositoryInterface::class)),

    // --- D. Controladores (Presentation Layer) ---
    TareaController::class => \DI\create(TareaController::class)
        ->constructor(\DI\get(TareaServiceInterface::class)),

    UsuarioController::class => \DI\create(UsuarioController::class)
        ->constructor(\DI\get(UsuarioServiceInterface::class)),

    ProyectoController::class => \DI\create(ProyectoController::class)
        ->constructor(\DI\get(ProyectoServiceInterface::class)),

    ReporteController::class => \DI\create(ReporteController::class)
        ->constructor(\DI\get(ReporteRepositoryInterface::class)),

    DataMasterController::class => \DI\create(DataMasterController::class)
        ->constructor(\DI\get(DataMasterRepositoryInterface::class)),
    
    // NUEVO: Controlador de Sucursales (Recibe su Servicio)
    SucursalController::class => \DI\create(SucursalController::class)
        ->constructor(\DI\get(SucursalServiceInterface::class)),
]);

return $builder->build();