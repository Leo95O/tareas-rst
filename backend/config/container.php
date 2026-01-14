<?php

use DI\ContainerBuilder;
use App\Config\Database;
use PDO;

// 1. Importar Interfaces (AJUSTADO A LAS CARPETAS CORRECTAS)
use App\Interfaces\DataMaster\DataMasterRepositoryInterface;
use App\Interfaces\LoginGuard\LoginGuardRepositoryInterface;
use App\Interfaces\Proyecto\ProyectoRepositoryInterface;
use App\Interfaces\Reporte\ReporteRepositoryInterface;
use App\Interfaces\Tarea\TareaRepositoryInterface;
use App\Interfaces\Usuario\UsuarioRepositoryInterface;

use App\Interfaces\LoginGuard\LoginGuardServiceInterface;
use App\Interfaces\Proyecto\ProyectoServiceInterface;
use App\Interfaces\Tarea\TareaServiceInterface;
use App\Interfaces\Usuario\UsuarioServiceInterface;

// 2. Importar Clases Concretas
use App\Repositories\DataMasterRepository;
use App\Repositories\LoginGuardRepository;
use App\Repositories\ProyectoRepository;
use App\Repositories\ReporteRepository;
use App\Repositories\TareaRepository;
use App\Repositories\UsuarioRepository;

use App\Services\LoginGuardService;
use App\Services\ProyectoService;
use App\Services\TareaService;
use App\Services\UsuarioService;

// 3. Importar Controladores
use App\Controllers\DataMasterController;
use App\Controllers\ProyectoController;
use App\Controllers\ReporteController;
use App\Controllers\TareaController;
use App\Controllers\UsuarioController;

$builder = new ContainerBuilder();

$builder->addDefinitions([

    // --- A. Base de Datos ---
    PDO::class => function () {
        return Database::getInstance()->getConnection();
    },

    // --- B. Repositorios (Data Layer) ---
    // Inyección manual del PDO en cada repositorio
    DataMasterRepositoryInterface::class => \DI\create(DataMasterRepository::class)->constructor(\DI\get(PDO::class)),
    LoginGuardRepositoryInterface::class => \DI\create(LoginGuardRepository::class)->constructor(\DI\get(PDO::class)),
    ProyectoRepositoryInterface::class   => \DI\create(ProyectoRepository::class)->constructor(\DI\get(PDO::class)),
    ReporteRepositoryInterface::class    => \DI\create(ReporteRepository::class)->constructor(\DI\get(PDO::class)),
    TareaRepositoryInterface::class      => \DI\create(TareaRepository::class)->constructor(\DI\get(PDO::class)),
    UsuarioRepositoryInterface::class    => \DI\create(UsuarioRepository::class)->constructor(\DI\get(PDO::class)),

    // --- C. Servicios (Business Layer) ---
    
    // LoginGuardService: Recibe su Repo
    LoginGuardServiceInterface::class => \DI\create(LoginGuardService::class)
        ->constructor(\DI\get(LoginGuardRepositoryInterface::class)),

    // ProyectoService: Recibe su Repo
    ProyectoServiceInterface::class => \DI\create(ProyectoService::class)
        ->constructor(\DI\get(ProyectoRepositoryInterface::class)),

    // TareaService: Recibe su Repo
    TareaServiceInterface::class => \DI\create(TareaService::class)
        ->constructor(\DI\get(TareaRepositoryInterface::class)),

    // ★ CORRECCIÓN IMPORTANTE AQUÍ:
    // UsuarioService: Recibe UsuarioRepo Y TAMBIÉN LoginGuardService
    UsuarioServiceInterface::class => \DI\create(UsuarioService::class)
        ->constructor(
            \DI\get(UsuarioRepositoryInterface::class),
            \DI\get(LoginGuardServiceInterface::class) // ¡Faltaba esto!
        ),

    // --- D. Controladores (Presentation Layer) ---
    
    // Tareas: Usa Servicio
    TareaController::class => \DI\create(TareaController::class)
        ->constructor(\DI\get(TareaServiceInterface::class)),

    // Usuarios: Usa Servicio
    UsuarioController::class => \DI\create(UsuarioController::class)
        ->constructor(\DI\get(UsuarioServiceInterface::class)),

    // Proyectos: Usa Servicio
    ProyectoController::class => \DI\create(ProyectoController::class)
        ->constructor(\DI\get(ProyectoServiceInterface::class)),

    // Reportes: Directo al Repo (Solo lectura)
    ReporteController::class => \DI\create(ReporteController::class)
        ->constructor(\DI\get(ReporteRepositoryInterface::class)),

    // DataMaster: Directo al Repo (Catálogos estáticos)
    DataMasterController::class => \DI\create(DataMasterController::class)
        ->constructor(\DI\get(DataMasterRepositoryInterface::class)),
]);

return $builder->build();