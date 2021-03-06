<?php

/**
 * This file is part of the Elcodi package.
 *
 * Copyright (c) 2014 Elcodi.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author Aldo Chiecchia <zimage@tiscali.it>
 */

namespace Elcodi\Component\Media\Transformer;

use Elcodi\Component\Media\Entity\Interfaces\FileInterface;
use Elcodi\Component\Media\Transformer\Interfaces\FileIdentifierTransformerInterface;

/**
 * Class FileIdentifierTransformer
 */
class FileIdentifierTransformer implements FileIdentifierTransformerInterface
{
    /**
     * Transforms an entity to be stored
     *
     * @param FileInterface $file File to transform
     *
     * @return string Entity transformed
     */
    public function transform(FileInterface $file)
    {
        return $file->getId() . '.' .
        $file->getExtension();
    }
}
