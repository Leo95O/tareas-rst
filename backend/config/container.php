<?php

use DI\ContainerBuilder;
use App\Config\Database;

// 1. Importar Interfaces
use App\Interfaces\DataMasterRepositoryInterface;
use App\Interfaces\LoginGuardRepositoryInterface;
use App\Interfaces\ProyectoRepositoryInterface;
use App\Interfaces\ReporteRepositoryInterface;
use App\Interfaces\TareaRepositoryInterface;
use App\Interfaces\UsuarioRepositoryInterface;

use App\Interfaces\LoginGuardServiceInterface;
use App\Interfaces\ProyectoServiceInterface;
use App\Interfaces\TareaServiceInterface;
use App\Interfaces\UsuarioServiceInterface;

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
    // Conectamos la Interfaz con la Implementación real y le inyectamos PDO
    DataMasterRepositoryInterface::class => \DI\create(DataMasterRepository::class)->constructor(\DI\get(PDO::class)),
    LoginGuardRepositoryInterface::class => \DI\create(LoginGuardRepository::class)->constructor(\DI\get(PDO::class)),
    ProyectoRepositoryInterface::class   => \DI\create(ProyectoRepository::class)->constructor(\DI\get(PDO::class)),
    ReporteRepositoryInterface::class    => \DI\create(ReporteRepository::class)->constructor(\DI\get(PDO::class)),
    TareaRepositoryInterface::class      => \DI\create(TareaRepository::class)->constructor(\DI\get(PDO::class)),
    UsuarioRepositoryInterface::class    => \DI\create(UsuarioRepository::class)->constructor(\DI\get(PDO::class)),

    // --- C. Servicios (Business Layer) ---
    // Inyectamos el Repositorio correspondiente (usando su Interfaz)
    LoginGuardServiceInterface::class => \DI\create(LoginGuardService::class)->constructor(\DI\get(LoginGuardRepositoryInterface::class)),
    ProyectoServiceInterface::class   => \DI\create(ProyectoService::class)->constructor(\DI\get(ProyectoRepositoryInterface::class)),
    TareaServiceInterface::class      => \DI\create(TareaService::class)->constructor(\DI\get(TareaRepositoryInterface::class)),
    UsuarioServiceInterface::class    => \DI\create(UsuarioService::class)->constructor(\DI\get(UsuarioRepositoryInterface::class)),

    // --- D. Controladores (Presentation Layer) ---
    // Inyectamos el Servicio o Repositorio según corresponda
    
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