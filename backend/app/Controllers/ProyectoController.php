<?php

namespace App\Controllers;

// 1. Usamos la Interfaz (Ajusta el namespace si tu interfaz está en otra carpeta)
use App\Interfaces\Proyecto\ProyectoServiceInterface; 
use App\Utils\ApiResponse;
use App\Validators\ProyectoValidator;
use \Slim\Slim;

class ProyectoController
{
    private $proyectoService;

    // 2. Inyección de Dependencias: Pedimos el Servicio
    public function __construct(ProyectoServiceInterface $service)
    {
        $this->proyectoService = $service;
    }

    public function listar()
    {
        try {
            $app = Slim::getInstance();
            $usuario = $app->usuario; 

            // El servicio inyectado ya sabe qué hacer
            $proyectos = $this->proyectoService->listarProyectos($usuario);

            $data = array_map(function ($p) {
                return $p->toArray();
            }, $proyectos);

            ApiResponse::exito("Listado de proyectos.", $data);
        } catch (\Exception $e) {
            ApiResponse::error($e->getMessage());
        }
    }

    public function obtenerPorId($id)
    {
        try {
            $proyecto = $this->proyectoService->obtenerProyectoPorId($id);

            ApiResponse::exito("Proyecto recuperado.", $proyecto->toArray());

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function crear()
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuario = $app->usuario;

            ProyectoValidator::validarCreacion($datos);

            $id = $this->proyectoService->crearProyecto($datos, $usuario);
            ApiResponse::exito("Proyecto creado.", ['id' => $id]);

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $app = Slim::getInstance();
            $datos = json_decode($app->request->getBody(), true);
            $usuario = $app->usuario;

            ProyectoValidator::validarEdicion($datos);

            $this->proyectoService->editarProyecto($id, $datos, $usuario);
            ApiResponse::exito("Proyecto actualizado.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            $app = Slim::getInstance();
            $usuario = $app->usuario;

            $this->proyectoService->eliminarProyecto($id, $usuario);
            ApiResponse::exito("Proyecto eliminado.");

        } catch (\Exception $e) {
            ApiResponse::alerta($e->getMessage());
        }
    }
}