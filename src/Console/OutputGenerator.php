<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Console;

use Vaimo\ComposerChangelogs\Exceptions\PackageResolverException;

class OutputGenerator
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(
        \Symfony\Component\Console\Output\OutputInterface $output = null
    ) {
        $this->output = $output;
    }

    public function writeResolverException(PackageResolverException $exception)
    {
        if (!$this->output) {
            return;
        }
        
        $messages = array_merge(
            array(sprintf('<error>%s</error>', $exception->getMessage())),
            array_filter((array)$exception->getExtraInfo())
        );
        
        array_walk($messages, array($this->output, 'writeln'));
    }
    
    public function writeLines(array $lines)
    {
        if (!$this->output) {
            return;
        }
        
        array_walk($lines, array($this->output, 'writeln'));
    }
}
