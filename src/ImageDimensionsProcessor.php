<?php

namespace Phauthentic\Infrastructure\Storage\Processor\Image;

use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\Processor\ProcessorInterface;

class ImageDimensionsProcessor implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process(FileInterface $file): FileInterface
    {
        $dimensions = @getimagesize($file->path());
        if (!$dimensions) {
            return $file;
        }

        $file = $file->withMetadataKey('width', $dimensions[0])
            ->withMetadataKey('height', $dimensions[1]);

        return $file;
    }
}
