<?php declare(strict_types=1);

namespace OAS\Utils;

use LogicException;
use phpDocumentor\Reflection\PseudoTypes\ArrayShape;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use TypeError;

// TODO : extract to separate package "biera/type-validator"
class TypeValidator
{
    /**
     * @param mixed $value
     * @param string|Type $type
     */
    public static function isValid($value, $type): bool
    {
        $type = is_string($type) ? (new TypeResolver())->resolve($type) : $type;

        if (!$type instanceof Type) {
            throw new TypeError('The "type" parameter must be \phpDocumentor\Reflection\Type|string of type');
        }

        if ($type instanceof Mixed_) {
            return true;
        }

        if ($type instanceof Null_) {
            return is_null($value);
        }

        if ($type instanceof Nullable) {
            if (is_null($value)) {
                return true;
            }

            return self::isValid($value, $type->getActualType());
        }

        if ($type instanceof Array_ || $type instanceof Iterable_) {
            if (!is_iterable($value)) {
                return false;
            }

            foreach ($value as $key => $item) {
                if (!self::isValid($key, $type->getKeyType()) || !self::isValid($item, $type->getValueType())) {
                    return false;
                }
            }

            return true;
        }

        if ($type instanceof ArrayShape) {
            if (!is_array($value)) {
                return false;
            }

            $itemTypes = $type->getItems();

            if (count($itemTypes) !== count($value)) {
                return false;
            }

            $index = 0;

            foreach ($value as $key => $item) {
                $itemShape = array_shift($itemTypes);
                $itemKey = $itemShape->getKey();

                if (!self::isValid($item, $itemShape->getValue()) || ($itemKey !== '' && $itemKey !== $key) || ($itemKey === '' && $index !== $key)) {
                    return false;
                }

                $index++;
            }

            return true;
        }

        if ($type instanceof Compound) {
            foreach ($type as $compoundTypeComponent) {
                if (self::isValid($value, $compoundTypeComponent)) {
                    return true;
                }
            }

            return false;
        }

        if ($type instanceof Object_) {
            return is_a($value, (string) $type->getFqsen());
        }

        if ($type instanceof String_) {
            return is_string($value);
        }

        if ($type instanceof Integer) {
            return is_int($value);
        }

        if ($type instanceof Float_) {
            return is_float($value);
        }

        if ($type instanceof Boolean) {
            return is_bool($value);
        }

        if ($type instanceof Null_) {
            return is_null($value);
        }

        throw new LogicException("Type $type not supported, please open an issue.");
    }

    /**
     * @param mixed $value
     * @param string|Type $type
     */
    public static function assertValid($value, $type, ?string $message = null): void
    {
        if (!self::isValid($value, $type)) {
            throw new TypeError($message ?? "Provided value must be $type type");
        }
    }
}