<?php declare(strict_types=1);

namespace OAS\Utils\ConstructorParametersResolver;

use RuntimeException;
use Throwable;

final class ValueResolvementException extends RuntimeException
{
    /**
     * @param array<Throwable>|null $errors
     */
    private function __construct(
        private string $path,
        private ?array $errors = null,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                "The following errors occurred when resolving parameter at '%s' path",
                join(
                    '.',
                    $this->buildPath($previous instanceof ValueResolvementException ? $previous->getPath() : [])
                )
            ),
            0,
            $previous
        );
    }

    public static function create(string $path, ValueResolvementException $previous): self
    {
        return new self($path, previous: $previous);
    }

    /**
     * @param array<Throwable> $errors
     */
    public static function createWithErrors(string $path, array $errors): self
    {
        return new self($path, $errors);
    }

    /**
     * @return array<int, string>
     */
    public function getPath(): array
    {
        return $this->buildPath();
    }

    /**
     * @return array<Throwable>
     */
    public function getErrors(): array
    {
        $deepest = $this;

        while (($previous = $deepest->getPrevious()) instanceof ValueResolvementException) {
            $deepest = $previous;
        }

        return $deepest->errors;
    }

    public function getFormattedMessage(): string
    {
        return $this->formatMessage() . "\n";
    }

    private function formatMessage(int $depth = 0): string
    {
        $errors = array_map(
            fn (Throwable $error) => $error instanceof ValueResolvementException
                ? $error->formatMessage($depth + 1)
                : sprintf("%s%s", $this->indent($depth +1), $error->getMessage()),
            $this->getErrors()
        );

        return sprintf(
            "%s%s:\n%s",
            $this->indent($depth),
            $this->getMessage(),
            join("\n", $errors)
        );
    }

    private function indent(int $depth): string
    {
        return join('', array_fill(0, $depth, "\t"));
    }

    private function buildPath(array $suffix = []): array
    {
        $path = array_merge([$this->path], $suffix);
        $previous = $this->getPrevious();

        while ($previous instanceof ValueResolvementException) {
            $path[] = $previous->path;
            $previous = $previous->getPrevious();
        }

        return $path;
    }
}
