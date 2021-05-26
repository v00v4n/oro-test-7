<?php

declare(strict_types=1);

/*
 * This file is part of the V00V4N OroTestTask7 Project.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use App\Bundle\ChainCommandBundle\CommandChain\CommandChainManager;
use Symfony\Bundle\FrameworkBundle\Console\Application as BaseApplication;

/**
 * Class Application
 * @package App
 */
class Application extends BaseApplication
{
    private bool $commandsRegistered = false;

    protected function registerCommands()
    {
        parent::registerCommands();

        if ($this->commandsRegistered) {
            return;
        }
        $this->commandsRegistered = true;

        (new CommandChainManager($this))->registerApplicationChains();
    }
}
