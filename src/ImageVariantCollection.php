<?php

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Phauthentic\Infrastructure\Storage\Processor\Image;

use ArrayIterator;
use Iterator;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantExistsException;
use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\UnsupportedOperationException;
use ReflectionClass;

/**
 * Conversion Collection
 */
class ImageVariantCollection implements ImageVariantCollectionInterface
{
    /**
     * @var array
     */
    protected array $variants = [];

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Workaround for php 8 because call_user_func_array will now behave this way:
     * args keys will now be interpreted as parameter names, instead of being silently ignored.
     *
     * @param object $object
     * @param string $method
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    protected static function filterArgs(object $object, string $method, array $args): array
    {
        $filteredArgs = [];
        $variantReflection = new ReflectionClass($object);
        $methodReflection = $variantReflection->getMethod($method);
        $reflectionParameters = $methodReflection->getParameters();

        foreach ($reflectionParameters as $parameter) {
            if (isset($args[$parameter->getName()])) {
                $filteredArgs[$parameter->getName()] = $args[$parameter->getName()];
            }
        }

        return $filteredArgs;
    }

    /**
     * @param array $variants Variant array structure
     * @return self
     */
    public static function fromArray(array $variants)
    {
        $that = new self();

        foreach ($variants as $name => $data) {
            $variant = ImageVariant::create($name);
            if (isset($data['optimize']) && $data['optimize'] === true) {
                $variant = $variant->optimize();
            }

            if (!empty($data['path']) && is_string($data['path'])) {
                $variant = $variant->withPath($data['path']);
            }

            foreach ($data['operations'] as $method => $args) {
                if (!method_exists($variant, $method)) {
                    UnsupportedOperationException::withName($method);
                }

                $variant = call_user_func_array(
                    [$variant, $method],
                    self::filterArgs($variant, $method, $args)
                );
            }

            $that->add($variant);
        }

        return $that;
    }

    /**
     * @param string $name Name
     * @return \Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant
     */
    public function addNew(string $name)
    {
        $this->add(ImageVariant::create($name));

        return $this->get($name);
    }

    /**
     * Gets a manipulation from the collection
     *
     * @param string $name
     * @return \Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant
     */
    public function get(string $name): ImageVariant
    {
        return $this->variants[$name];
    }

    /**
     * @param \Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant $variant Variant
     * @return void
     */
    public function add(ImageVariant $variant): void
    {
        if ($this->has($variant->name())) {
            throw VariantExistsException::withName($variant->name());
        }

        $this->variants[$variant->name()] = $variant;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->variants[$name]);
    }

    /**
     * @param string $name
     */
    public function remove(string $name): void
    {
        unset($this->variants[$name]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->variants);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->variants as $variant) {
            $array[$variant->name()] = $variant->toArray();
        }

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->variants);
    }
}
