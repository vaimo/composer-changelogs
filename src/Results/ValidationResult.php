<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Results;

class ValidationResult
{
    /**
     * @var bool
     */
    private $result;

    /**
     * @var string[]
     */
    private $messages;

    /**
     * @param bool $result
     * @param array $messages
     */
    public function __construct(
        $result,
        array $messages = array()
    ) {
        $this->result = $result;
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function __invoke()
    {
        return $this->result;
    }
}
