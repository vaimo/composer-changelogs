<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Extractors;

class ErrorExtractor
{
    public function extractMessages(\Exception $exception, $addIndexes = false)
    {
        $messages = array();

        do {
            $messages[] = $exception->getMessage();
        } while ($exception = $exception->getPrevious());

        if ($addIndexes) {
            array_walk($messages, function (&$message, $index) {
                $message = sprintf('#%s %s', $index, $message);
            });
        }

        return $messages;
    }
}
