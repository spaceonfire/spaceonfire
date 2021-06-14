<?php

declare(strict_types=1);

namespace spaceonfire\DevTool\Monorepo;

use Symfony\Component\Console\Command\Command;

final class MonorepoSplitCommand extends Command
{
    protected static $defaultName = 'monorepo:split';
}
