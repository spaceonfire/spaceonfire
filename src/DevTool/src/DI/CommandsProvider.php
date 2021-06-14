<?php

declare(strict_types=1);

namespace spaceonfire\DevTool\DI;

use spaceonfire\Container\Definition\DefinitionTag;
use spaceonfire\Container\ServiceProvider\AbstractServiceProvider;
use spaceonfire\DevTool\ChangeLog\ChangeLogCommand;
use spaceonfire\DevTool\Monorepo\Composer\MonorepoComposerCommand;
use spaceonfire\DevTool\Monorepo\MonorepoSplitCommand;
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
        ];
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->add(MoveClassCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->add(ChangeLogCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->add(MonorepoComposerCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->add(MonorepoSplitCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
        $this->getContainer()->add(VersionCommand::class)->addTag(DefinitionTag::CONSOLE_COMMAND);
    }
}
