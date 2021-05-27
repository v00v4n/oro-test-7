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

namespace OroTest\Bundle\CommandChainBundle\CommandChain;

/**
 * Class LogFormatter
 * @package OroTest\Bundle\CommandChainBundle\CommandChain
 */
class LogFormatter
{
    public const DATETIME_FORMAT  = 'Y-m-d H:i:s';

    public function formatLog(string $level, string $message, array $context): string
    {
        $lines = \preg_split('/\n/', $message);
        $lines = \array_map('trim', $lines);
        $date = \date('[' . static::DATETIME_FORMAT . '] ');

        return $date . \implode(\PHP_EOL . $date, $lines) . \PHP_EOL;
    }
}
