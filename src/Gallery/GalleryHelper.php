<?php

namespace Sovic\Gallery\Gallery;

use Sovic\Gallery\Entity\GalleryItem;

class GalleryHelper
{
    public const SIZE_THUMB = 'small';
    public const SIZE_BIG = 'big';
    public const SIZE_FULL = 'full';

    public const SIZES = [
        self::SIZE_THUMB,
        self::SIZE_BIG,
        self::SIZE_FULL,
    ];

    // simplify sizes selection with predefined sets
    public const SIZES_SET_ALL = [
        self::SIZE_THUMB,
        self::SIZE_BIG,
        self::SIZE_FULL,
    ];

    public static function getMediaPaths(
        GalleryItem $item,
        string      $baseUrl = '',
        array       $sizes = [self::SIZE_THUMB, self::SIZE_BIG]
    ): array
    {
        $dir = sprintf('%04d', (int) ($item->getId() / 100));
        $basePath = $baseUrl . '/content/' . $item->getModel() . '/' . $dir;
        $result = [];
        foreach ($sizes as $size) {
            if (!in_array($size, self::SIZES, true)) {
                continue;
            }
            if ($size === self::SIZE_FULL) {
                $result[$size] = $basePath . '/' . $item->getId() . '.' . $item->getExtension();
            } else {
                $result[$size] = $basePath . '/' . $item->getId() . '_' . $size . '.' . $item->getExtension();
            }
        }

        return $result;
    }
}
