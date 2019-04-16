<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Utils;

class SystemUtils
{
    public function getCommandStdOut($command, $cwd, $default = '')
    {
        $process = new \Symfony\Component\Process\Process($command, $cwd);

        $process->setTimeout(null);

        try {
            $process->mustRun();

            return $process->getOutput();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
            return $default;
        }
    }
}
