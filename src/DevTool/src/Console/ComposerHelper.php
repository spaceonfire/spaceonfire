<?php

declare(strict_types=1);

namespace spaceonfire\DevTool\Console;

use spaceonfire\DevTool\Monorepo\Composer\ComposerJson;
use Symfony\Component\Console\Helper\Helper;

final class ComposerHelper extends Helper
{
    public const NAME = 'composer';

    private string $composerJsonPath;

    private ?ComposerJson $composerJson = null;

    public function __construct(string $composerJsonPath)
    {
        $this->composerJsonPath = $composerJsonPath;
    }

    public function getComposerJson(): ComposerJson
    {
        return $this->composerJson ??= ComposerJson::read($this->composerJsonPath);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
