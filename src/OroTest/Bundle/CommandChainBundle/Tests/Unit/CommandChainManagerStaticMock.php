<?php
declare(strict_types=1);

/*
 * This file is part of the phpvv package.
 *
 * (c) Volodymyr Sarnytskyi <v00v4n@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace OroTest\Bundle\CommandChainBundle\Tests\Unit;

use OroTest\Bundle\CommandChainBundle\CommandChain\CommandChainManager;

/**
 * Class CommandChainManagerStaticMock
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Unit
 */
class CommandChainManagerStaticMock extends CommandChainManager
{
    public static int $callsCount = 0;

    public function registerApplicationChains()
    {
        self::$callsCount++;
    }
}
