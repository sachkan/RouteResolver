<?php
/**
 * Class routeResolver
 * @author sac <sacchkkan@gmail.com>
 * @version 1.0.0
 */

namespace App;

class RouteResolver
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function call($request, $response, $args)
    {
        $args = explode('/', $args['args']);

        $reflection = $this->getControllerClass($args);
        if (!$reflection) throw new \Exception("Class '$args[0]' not found");

        $method = $this->getMethod($reflection, $args);
        if (!$method) throw new \Exception('Method ' . $method->getName() . ' not found in ' . $reflection->getName());

        $oClass = $reflection->newInstanceArgs([$this->container, $request, $response]);

        $outputData = $method->invokeArgs($oClass, [$request, $response, $args]);

        return $response->write($outputData)->withStatus(200);
    }

    private function getControllerClass(&$args)
    {
        $reflection = false;

        // test if argument 1 is a controller
        if (@$args[1]) $reflection = $this->getReflection($args, 1);

        // test if argument 0 is a controller
        if (@$args[0] && !$reflection) $reflection = $this->getReflection($args, 0);

        // get default controller
        if (!$reflection) $reflection = $this->getReflection($args);

        return $reflection;
    }

    private function getReflection(&$args, $argNr = -1)
    {
        $domain = '';
        if ($argNr > 1) {
            $controller = implode("\\", array_map('ucfirst', $args));
        } elseif ($argNr == 1) {
            $domain = '\\'.ucfirst($args[0]);
            $controller = ucfirst($args[1]);
        } elseif ($argNr == 0) {
            $controller = ucfirst($args[0]);
        } else {
            $controller = 'defaultClass'; // set default class
        }

        $route = $domain . '\\'. $controller ;
        $fullControllerName = 'App\\'. $route;

        try {
            $reflection = new \ReflectionClass($fullControllerName);

            for ($i = 0; $i <= $argNr; $i++){
                unset($args[$i]);
            }
            $args = array_values($args);
        } catch (\ReflectionException $e) {
            $reflection = 0;
        }
        return $reflection;
    }

    private function getMethod($reflection, &$args)
    {
        if ($args)
            $func = $args[0];
        else
            $func = 'defaultMethod'; // set default method of class

        try {
            unset($args[0]);
            $args = array_values($args);
            $method = $reflection->getMethod($func);
        } catch (\ReflectionException $e) {
            $args[0] = $func;
            $method = 0;
        }

        return $method;
    }
}
