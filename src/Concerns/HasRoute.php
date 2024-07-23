<?php

namespace Saidqb\CodeigniterSupport\Concerns;

use CodeIgniter\Config\Services;
use CodeIgniter\Commands\Utilities\Routes\AutoRouteCollector;
use Config\Api;
use Saidqb\CorePhp\Lib\Str;

trait HasRoute
{
    protected $routeAll = [];

    protected function routeAutoGetAll()
    {
        $collection = service('routes')->loadRoutes();

        $autoRouteCollector = new AutoRouteCollector(
            $collection->getDefaultNamespace(),
            $collection->getDefaultController(),
            $collection->getDefaultMethod()
        );

        return $autoRouteCollector->get();
    }

    protected function getAllControllerName()
    {

        $routes = $this->routeAutoGetAll();
        $controllers = [];
        foreach ($routes as $route) {
            $controllerName = $route[3];

            $checkExclude = [];
            foreach (Api::$controllerEndpoint['exclude'] as $exclude) {
                if (str_contains($controllerName, $exclude)) {
                    $checkExclude[] = true;
                } else {
                    $checkExclude[] = false;
                }
            }

            if (in_array(true, $checkExclude)) {
                continue;
            }

            $checkInclude = [];
            foreach (Api::$controllerEndpoint['include'] as $include) {
                if (str_contains($controllerName, $include)) $checkInclude[] = true;
            }

            if (in_array(true, $checkInclude)) {
                $controllers[] = $controllerName;
            }
        }
        return array_unique($controllers);
    }


    protected function currentControllerExistArray($arrayControllerExist = [])
    {
        $currentController = $this->routeCurrentControllerName();
        if (in_array($currentController, $arrayControllerExist)) {
            return true;
        }

        $checkExist = [];
        foreach ($arrayControllerExist as $contain) {
            if (str_contains($currentController, $contain)) $checkExist[] = true;
        }

        if (in_array(true, $checkExist)) {
            return true;
        }

        return false;
    }

    protected function routeEndpointList()
    {
        $arr = $this->getAllControllerName();
        $endpoints = [];
        foreach ($arr as $controller) {
            $endpoint = str_replace('\App\Controllers\\', '', $controller);
            $endpointArr = explode('::', $endpoint);
            $baseArr = explode('\\', $endpointArr[0]);

            $endpoint = str_replace($baseArr[0] . '\\', '', $endpointArr[0]);

            $method = '';
            if (isset($endpointArr[1])) {
                $method = $endpointArr[1];
            }


            $endpoint = str_replace('\\', '-', $endpoint);
            $label = str_replace('\\', ' ', $endpoint);
            $label = Str::toTitleCase($label);
            $endpoints[] = [
                'base' => $baseArr[0],
                'endpoint' => $endpoint,
                'method' => $method,
                'label' => $label,
            ];
        }
        return $endpoints;
    }

    protected function routeCurrentControllerName()
    {
        return Services::router()->controllerName();
    }

    protected function routeCurrentMethodName()
    {
        return Services::router()->methodName();
    }
}
