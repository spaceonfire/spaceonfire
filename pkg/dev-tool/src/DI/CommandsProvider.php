<?php

declare(strict_types=1);

namespace spaceonfire\DevTool\DI;

use spaceonfire\Container\Factory\DefinitionTag;
use spaceonfire\Container\ServiceProvider\AbstractServiceProvider;
use spaceonfire\DevTool\ChangeLog\ChangeLogCommand;
use spaceonfire\DevTool\Monorepo\Composer\MonorepoComposerCommand;
use spaceonfire\DevTool\Monorepo\MonorepoSplitCommand;
use spaceonfire\DevTool\Monorepo\TestCommand;
use spaceonfire\DevTool\Refactor\MoveClass\MoveClassCommand;
use spaceonfire\DevTool\Version\VersionCommand;

final class CommandsProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            DefinitionTag::CONSOLE_COMMAND,
            MoveClassCommand::class,
            ChangeLogCommand::class,
            MonorepoComposerCommand::class,
            MonorepoSplitCommand::class,
            VersionCommand::class,
            TestCommand::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->define(MoveClassCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(ChangeLogCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(MonorepoComposerCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(MonorepoSplitCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(VersionCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->define(TestCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
    }
}
