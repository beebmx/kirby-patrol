<?php

namespace Beebmx\KirbyPatrol\Support;

use Closure;
use Kirby\Exception\Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;

class Pipeline
{
    protected mixed $passable;

    protected array $pipes = [];

    protected string $method = 'handle';

    public function __construct() {}

    public function send(mixed $passable): static
    {
        $this->passable = $passable;

        return $this;
    }

    public function through(array $pipes): static
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function via(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function then(Closure $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes()),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->passable);
    }

    public function execute(?Closure $destination = null)
    {
        return $this->then(
            $destination instanceof Closure
                ? $destination
                : fn ($passable) => $passable
        );
    }

    protected function pipes(): array
    {
        return $this->pipes;
    }

    public function pipe(array $pipes): static
    {
        array_push($this->pipes, ...$pipes);

        return $this;
    }

    protected function prepareDestination(Closure $destination): Closure
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                return $this->handleException($passable, $e);
            }
        };
    }

    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    if (is_callable($pipe)) {
                        return $pipe($passable, $stack);
                    } elseif (! is_object($pipe)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);

                        $pipe = $this->make($name);

                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        $parameters = [$passable, $stack];
                    }

                    $carry = method_exists($pipe, $this->method)
                        ? $pipe->{$this->method}(...$parameters)
                        : $pipe(...$parameters);

                    return $this->handleCarry($carry);
                } catch (Throwable $e) {
                    return $this->handleException($passable, $e);
                }
            };
        };
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function make($concrete)
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new Exception("Class [$concrete] does not exist.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        try {
            $instances = $this->resolveDependencies($dependencies);
        } catch (Exception $e) {
            throw new Exception('Theres an error with dependecies.');
        }

        return $reflector->newInstanceArgs($instances);
    }

    protected function parsePipeString(string $pipe): array
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    protected function handleCarry(mixed $carry): mixed
    {
        return $carry;
    }

    /**
     * @throws Throwable
     */
    protected function handleException(mixed $passable, Throwable $e): mixed
    {
        throw $e;
    }
}
