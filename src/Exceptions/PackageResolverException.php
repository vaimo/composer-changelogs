<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Exceptions;

class PackageResolverException extends \Exception
{
    /**
     * @var mixed
     */
    private $extraInfo;

    public function setExtraInfo($value)
    {
        $this->extraInfo = $value;
    }

    public function getExtraInfo()
    {
        return $this->extraInfo;
    }
}
