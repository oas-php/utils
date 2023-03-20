<?php declare(strict_types=1);

namespace OAS\Utils;

use OAS\Utils\ConstructorParametersResolver\CompoundTypeValueResolvementException;
use OAS\Utils\ConstructorParametersResolver\Event\AfterParamsResolution;
use OAS\Utils\ConstructorParametersResolver\Event\AfterParamValueResolution;
use OAS\Utils\ConstructorParametersResolver\Event\AfterValueResolution;
use OAS\Utils\ConstructorParametersResolver\Event\BeforeParamsResolution;
use OAS\Utils\ConstructorParametersResolver\Event\BeforeParamValueResolution;
use OAS\Utils\ConstructorParametersResolver\Event\BeforeValueResolution;
use OAS\Utils\ConstructorParametersResolver\InvalidTypeException;
use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionClass;
use OAS\Utils\ConstructorParametersResolver\Reflection\ReflectionParameter;
use OAS\Utils\ConstructorParametersResolver\ValueResolvementException;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionException;
use Throwable;
use TypeError;

class ConstructorParametersResolver
{
    private array $reflectionsCache = [];

    public function __construct(private ?EventDispatcherInterface $dispatcher = null)
    {
    }

    /**
     * @template T
     * @param array<string, mixed> $rawParams
     * @param callable(ReflectionClass, array<string, mixed>): T $builder
     * @return T
     * @throws ReflectionException
     */
    public function resolve(string $type, array $rawParams, callable $builder)
    {
        $reflection = $this->getReflection($type);
        $beforeResolution = $this->dispatch(new BeforeParamsResolution($reflection, $rawParams));
        $resolvedParams = $this->resolveConstructorParameters($reflection, $beforeResolution->getParams(), $builder);
        $afterResolution = $this->dispatch(new AfterParamsResolution($reflection, $resolvedParams));

        return call_user_func($builder, $reflection, array_values($afterResolution->getParams()));
    }

    /**
     * @param array<string, mixed> $rawValues
     * @throws ReflectionException
     */
    private function resolveConstructorParameters(ReflectionClass $reflection, array $rawValues, callable $builder): array
    {
        $constructorParameters = $reflection->getConstructor()->getParameters();

        return array_combine(
            array_map(
                fn (ReflectionParameter $reflectionParameter): string => $reflectionParameter->getName(),
                $constructorParameters
            ),
            array_map(
                function (ReflectionParameter $reflectionParameter) use ($rawValues, $builder) {
                    $name = $reflectionParameter->getName();
                    $type = $reflectionParameter->getExtendedType();
                    $rawValue = $rawValues[$name] ?? null;

                    $beforeParamResolution = $this->dispatch(new BeforeParamValueResolution($name, $type, $rawValue));

                    if ($beforeParamResolution->isValueResolved()) {
                        $value = $beforeParamResolution->getValue();
                    } else {
                        $isPassed = array_key_exists($name, $rawValues) || $beforeParamResolution->isRawValueUpdated();

                        try {
                            $value = !$isPassed && $reflectionParameter->isDefaultValueAvailable()
                                ? $reflectionParameter->getDefaultValue()
                                : $this->resolveConstructorParameter($type, $beforeParamResolution->getRawValue(), $builder);
                        } catch (ValueResolvementException $exception) {
                            throw ValueResolvementException::create($name, $exception);
                        } catch (CompoundTypeValueResolvementException $exception) {
                            throw ValueResolvementException::createWithErrors($name, $exception->errors);
                        } catch (Throwable $exception) {
                            throw ValueResolvementException::createWithErrors($name, [$exception]);
                        }
                    }

                    return $this->dispatch(new AfterParamValueResolution($name, $type, $value))->getValue();
                },
                $constructorParameters
            )
        );
    }

    /**
     * @param callable(ReflectionClass, array<string, mixed>): mixed $builder
     * @throws InvalidTypeException
     * @throws ReflectionException
     */
    private function resolveConstructorParameter(Type $type, mixed $rawValue, callable $builder): mixed
    {
        if ($type instanceof Nullable && $rawValue !== null) {
            return $this->resolveConstructorParameter($type->getActualType(), $rawValue, $builder);
        }

        if ($type instanceof Array_ || $type instanceof Iterable_) {
            if (!is_array($rawValue)) {
                throw new InvalidTypeException($type, $rawValue);
            }

            return array_map(
                function ($listElement, $index) use ($type, $builder) {
                    try {
                        return $this->resolveConstructorParameter($type->getValueType(), $listElement, $builder);
                    } catch (ValueResolvementException $exception) {
                        throw ValueResolvementException::create("$index", $exception);
                    } catch (CompoundTypeValueResolvementException $exception) {
                        throw ValueResolvementException::createWithErrors("$index", $exception->errors);
                    } catch (TypeError $exception) {
                        throw ValueResolvementException::createWithErrors("$index", [$exception]);
                    }
                },
                $rawValue,
                array_keys($rawValue)
            );
        }

        if ($type instanceof Compound) {
            $errors = [];
            // try to resolve parameter value using types in order they are provided
            foreach ($type->getIterator() as $compoundTypeComponent) {
                try {
                    return $this->resolveConstructorParameter($compoundTypeComponent, $rawValue, $builder);
                } catch (Throwable $error) {
                    $errors[] = $error;
                    continue;
                }
            }

            throw new CompoundTypeValueResolvementException($errors);
        }

        $beforeValueResolution = $this->dispatch(new BeforeValueResolution($type, $rawValue));

        if ($beforeValueResolution->isValueResolved()) {
            $value = $beforeValueResolution->getValue();
        } else {
            if ($type instanceof Object_) {
                $value = $this->resolve((string) $type->getFqsen(), $beforeValueResolution->getRawValue(), $builder);
            } else {
                $value = $beforeValueResolution->getRawValue();
            }
        }

        $resolvedValue = $this->dispatch(new AfterValueResolution($type, $value))->getValue();

        $this->assertTypeValid($type, $resolvedValue);

        return $resolvedValue;
    }

    /**
     * @template Event
     * @param Event $event
     * @return Event
     */
    private function dispatch(object $event): object
    {
        if (!is_null($this->dispatcher)) {
            $this->dispatcher->dispatch($event);
        }

        return $event;
    }

    private function assertTypeValid(Type $type, mixed $value): void
    {
        $invalid = ($type instanceof String_ && !is_string($value))
            || ($type instanceof Integer && !is_int($value))
            || ($type instanceof Float_ && !is_float($value))
            || ($type instanceof Boolean && !is_bool($value));

        if ($invalid) {
            throw new InvalidTypeException($type, $value);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function getReflection(string $class): ReflectionClass
    {
        return $this->reflectionsCache[$class] = $this->reflectionsCache[$class] ?? new ReflectionClass($class);
    }
}
