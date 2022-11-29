<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ComposerChangelogs\Resolvers;

class VcsDetailsResolver
{
    /**
     * @var \Vaimo\ComposerChangelogs\Utils\SystemUtils
     */
    private $systemUtils;

    /**
     * @var \Vaimo\ComposerChangelogs\Utils\PathUtils
     */
    private $pathUtils;

    /**
     * @var string[][]
     */
    private $linkTemplates = array(
        'bitbucket' => array(
            'link' => '{base}/src/{higher}',
            'diff' => '{base}/branches/compare/{higher}..{lower}#diff'
        ),
        'github' => array(
            'link' => '{base}/tree/{higher}',
            'diff' => '{base}/compare/{lower}...{higher}'
        )
    );

    /**
     * @var string[]
     */
    private $dateQueryTemplates = array(
        '.hg' => 'hg log --rev \'{version}\' --template=\'{date|isodate}\'',
        '.git' => 'git log {version}~1..{version} --simplify-by-decoration --pretty=format:"%ai"',
    );

    private $headQueryTemplates = array(
        '.hg' => 'hg log -r "branch(default) and 0:" -l 1 --template "{node}"',
        '.git' => 'git rev-list --max-parents=0 HEAD'
    );

    public function __construct()
    {
        $this->systemUtils = new \Vaimo\ComposerChangelogs\Utils\SystemUtils();
        $this->pathUtils = new \Vaimo\ComposerChangelogs\Utils\PathUtils();
    }

    public function resolveInitialCommitReference($repositoryRoot)
    {
        $result = '0';

        foreach ($this->headQueryTemplates as $folder => $command) {
            if (!file_exists($this->pathUtils->composePath($repositoryRoot, $folder))) {
                continue;
            }

            $result = $this->systemUtils->getCommandStdOut($command, $repositoryRoot, '0');
        }

        return trim($result);
    }

    public function resolveReleaseLinks($repositoryUrl, $version, $lastVersion = '')
    {
        if (!$repositoryUrl) {
            return array();
        }

        $urlComponents = parse_url($repositoryUrl);

        if (!isset($urlComponents['host'])) {
            return array();
        }

        $hostCode = strtok($urlComponents['host'], '.');

        if (!isset($this->linkTemplates[$hostCode])) {
            return array();
        }

        $data = array();

        foreach ($this->linkTemplates[$hostCode] as $code => $template) {
            $data[$code] = str_replace(
                array('{base}', '{higher}', '{lower}'),
                array($repositoryUrl, $version, $lastVersion ?: '0'),
                $template
            );
        }

        return $data;
    }

    public function resolveReleaseTime($repositoryRoot, $version)
    {
        if (!$repositoryRoot) {
            return array();
        }

        foreach ($this->dateQueryTemplates as $folder => $commandTemplate) {
            if (!file_exists($this->pathUtils->composePath($repositoryRoot, $folder))) {
                continue;
            }

            try {
                $result = $this->systemUtils->getCommandStdOut(
                    str_replace('{version}', $version, $commandTemplate),
                    $repositoryRoot
                );
            } catch (\Exception $exception) {
                return array();
            }

            if (!$result) {
                return array();
            }

            $segments = explode(' ', trim($result, " \t\n\r\0\x0B\""));

            return array(
                'date' => array_shift($segments),
                'time' => array_shift($segments)
            );
        }

        return array();
    }
}
