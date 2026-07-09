<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;
use Exception;

class Container
{
    private array $bindings = [];
    private array $instances = [];
    private static ?Container $instance = null;

    /**
     * Паттерн Синглтон для самого контейнера, чтобы иметь к нему доступ в ядре
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Связать абстракцию (интерфейс) с конкретной реализацией или колбэком
     */
    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared'   => $shared
        ];
    }

    /**
     * Зарегистрировать уже готовый объект как Синглтон (напр. PDO или Redis)
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Разрешить (собрать) зависимость
     */
    public function make(string $abstract): mixed
    {
        // 1. Если это уже собранный синглтон — отдаем его сразу
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $abstract;
        $shared = false;

        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            $shared = $this->bindings[$abstract]['shared'];
        }

        // 2. Если это замыкание (колбэк), вызываем его, передавая сам контейнер
        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            // 3. Иначе запускаем автоматическую сборку (Автовайринг)
            $object = $this->build($concrete);
        }

        // 4. Если это синглтон, запоминаем его на будущее
        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Магия Автовайринга через Рефлексию
     */
    private function build(string $concrete): mixed
    {
        if (!class_exists($concrete)) {
            throw new Exception("Класс {$concrete} не существует для сборки в DI", 500);
        }

        $reflector = new ReflectionClass($concrete);

        // Проверяем, можно ли вообще создать объект
        if (!$reflector->isInstantiable()) {
            throw new Exception("Класс {$concrete} не может быть инициализирован (интерфейс или абстрактный класс)", 500);
        }

        $constructor = $reflector->getConstructor();

        // Если конструктора нет — класс чистый, просто создаем его через new
        if ($constructor === null) {
            return new $concrete();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        // Рекурсивно собираем каждый параметр конструктора
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                throw new Exception("Не удалось разрешить параметр {$parameter->getName()} в классе {$concrete}: пропущен тайпхинт", 500);
            }

            if (!($type instanceof ReflectionNamedType)) {
                throw new Exception("Неподдерживаемый тип параметра для автовайринга в классе {$concrete}", 500);
            }

            if ($type->isBuiltin()) {
                // Если параметр — это обычная строка/инт (например, путь к папке), и у него есть дефолтное значение
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new Exception("Контейнер не знает, какую встроенную переменную ({$type->getName()}) передать в {$concrete}", 500);
            }

            // Если параметр — это класс/интерфейс, рекурсивно запрашиваем его у контейнера
            try {
                $dependencies[] = $this->make($type->getName());
            } catch (Exception $e) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw $e;
                }
            }
        }

        // Создаем экземпляр класса, передавая собранный массив зависимостей
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Вызывает колбэк или метод класса с автоматическим разрешением зависимостей
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            $callback = explode('@', $callback);
        }

        if (is_array($callback)) {
            $reflector = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflector = new \ReflectionFunction($callback instanceof \Closure ? $callback : \Closure::fromCallable($callback));
        }

        $dependencies = [];
        foreach ($reflector->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (array_key_exists($name, $parameters)) {
                $value = $parameters[$name];
                
                if ($type && $type instanceof ReflectionNamedType && $type->isBuiltin()) {
                    $typeName = $type->getName();
                    if ($typeName === 'int') {
                        $value = (int) $value;
                    } elseif ($typeName === 'float') {
                        $value = (float) $value;
                    } elseif ($typeName === 'bool') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    }
                }
                
                $dependencies[] = $value;
                continue;
            }

            if ($type && $type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                // Если тип совпадает с одним из переданных параметров по типу (например Request)
                $resolvedFromParams = false;
                foreach ($parameters as $param) {
                    if (is_object($param) && get_class($param) === $type->getName()) {
                        $dependencies[] = $param;
                        $resolvedFromParams = true;
                        break;
                    }
                }
                
                if (!$resolvedFromParams) {
                    $dependencies[] = $this->make($type->getName());
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new Exception("Не удалось разрешить параметр {$name} для вызова");
            }
        }

        if (is_array($callback)) {
            return $reflector->invokeArgs($callback[0], $dependencies);
        }

        return $reflector->invokeArgs($dependencies);
    }
}