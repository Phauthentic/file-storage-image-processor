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

use InvalidArgumentException;
use Phauthentic\Infrastructure\Storage\Processor\Variant;

/**
 * Image Manipulation
 */
class ImageVariant extends Variant
{
    protected string $name;

    /**
     * @var array<int, string>
     */
    protected array $operations;

    protected string $path = '';
    protected bool $optimize = false;
    protected string $url = '';

    public const FLIP_HORIZONTAL = 'h';
    public const FLIP_VERTICAL = 'v';

    /**
     * @param string $name Name
     * @return self
     */
    public static function create(string $name): self
    {
        $self = new self();
        $self->name = $name;

        return $self;
    }

    /**
     * Try to apply image optimizations if available on the system
     *
     * @return $this
     */
    public function optimize(): self
    {
        $this->optimize = true;

        return $this;
    }

    /**
     * @param int $height Width
     * @param int|null $width Height
     * @param int|null $x X
     * @param int|null $y Y
     * @return $this
     */
    public function crop(int $height, ?int $width = null, ?int $x = null, ?int $y = null): self
    {
        $this->operations['crop'] = [
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y
        ];

        return $this;
    }

    /**
     * @param int $amount Angle
     * @return $this
     */
    public function sharpen(int $amount): self
    {
        $this->operations['sharpen'] = [
            'amount' => $amount,
        ];

        return $this;
    }

    /**
     * @param int $angle Angle
     * @return $this
     */
    public function rotate(int $angle): self
    {
        $this->operations['rotate'] = [
            'angle' => $angle,
        ];

        return $this;
    }

    /**
     * @param int $height Height
     * @param boolean $preventUpscale Prevent Upscaling
     * @return $this
     */
    public function heighten(int $height, bool $preventUpscale = false): self
    {
        $this->operations['heighten'] = [
            'height' => $height,
            'preventUpscale' => $preventUpscale
        ];

        return $this;
    }

    /**
     * @param int $width Width
     * @param boolean $preventUpscale Prevent Upscaling
     * @return $this
     */
    public function widen(int $width, bool $preventUpscale = false): self
    {
        $this->operations['widen'] = [
            'width' => $width,
            'preventUpscale' => $preventUpscale
        ];

        return $this;
    }

    /**
     * @param int $width Width
     * @param int $height Height
     * @param bool $aspectRatio Keeps the aspect ratio
     * @param bool $preventUpscale Prevents upscaling
     * @return $this
     */
    public function resize(int $width, int $height, bool $aspectRatio = true, bool $preventUpscale = false): self
    {
        $this->operations['resize'] = [
            'width' => $width,
            'height' => $height,
            'aspectRatio' => $aspectRatio,
            'preventUpscale' => $preventUpscale
        ];

        return $this;
    }

    /**
     * Flips the image horizontal
     *
     * @return $this
     */
    public function flipHorizontal(): self
    {
        $this->operations['flipHorizontal'] = [
            'direction' => self::FLIP_HORIZONTAL
        ];

        return $this;
    }

    /**
     * Flips the image vertical
     *
     * @return $this
     */
    public function flipVertical(): self
    {
        $this->operations['flipVertical'] = [
            'direction' => self::FLIP_VERTICAL
        ];

        return $this;
    }

    /**
     * Flips the image
     *
     * @param string $direction Direction, h or v
     * @return $this
     */
    public function flip(string $direction): self
    {
        if ($direction !== 'h' && $direction !== 'v') {
            throw new InvalidArgumentException(sprintf(
                '`%s` is invalid, provide `h` or `v`',
                $direction
            ));
        }

        $this->operations['flip'] = [
            'direction' => $direction
        ];

        return $this;
    }

    /**
     * Allows the declaration of a callable that gets the image manager instance
     * and the arguments passed to it.
     *
     * @param callable $callback callback
     * @return $this
     */
    public function callback(callable $callback): self
    {
        $this->operations['callback'] = [
            'callback' => $callback
        ];

        return $this;
    }

    /**
     * @link http://image.intervention.io/api/fit
     * @param int $width Width
     * @param int|null $height Height
     * @param callable|null $callback Callback
     * @param bool $preventUpscale Prevent Upscaling
     * @param string $position Position
     * @return $this
     */
    public function fit(
        int $width,
        ?int $height = null,
        ?callable $callback = null,
        bool $preventUpscale = false,
        string $position = 'center'
    ): self {
        $this->operations['fit'] = [
            'width' => $width,
            'height' => $height,
            'callback' => $callback,
            'preventUpscale' => $preventUpscale,
            'position' => $position
        ];

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'operations' => $this->operations,
            'path' => $this->path,
            'url' => $this->url,
            'optimize' => $this->optimize,
        ];
    }
}
