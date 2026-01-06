<?php

namespace App\Controllers;

use App\Services\ProyectoService;
use App\Utils\ApiResponse;
use App\Validators\ProyectoValidator;
use \Slim\Slim;

class ProyectoController
{
    private $proyectoService;

    public function __construct()
    {
        $this->proyectoService = new ProyectoService();
    }

    public function listar()
    {
        try {
            $app = Slim::getInstance();
            $usuario = $app->usuario; // Usuario autenticado (inyectado por middleware)

            // El servicio filtra según el rol del usuario
            $proyectos = $this->proyectoService->listarProyectos($usuario);

            // Convertir a array
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
            $usuario = $app->usuario; // Inyectado por Middleware

            ProyectoValidator::validarCreacion($datos);

            $id = $this->proyectoService->crearProyecto($datos, $usuario);
            ApiResponse::exito("Proyecto creado.", ['id' => $id]);

        } catch (\Exception $e) {
            // Muestra que no tiene permisos si es rol 3
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