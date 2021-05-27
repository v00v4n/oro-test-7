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

namespace App\Bundle\ChainCommandBundle\CommandChain;

/**
 * Class LogFormatter
 * @package App\Bundle\ChainCommandBundle\CommandChain
 */
class LogFormatter
{
    public function formatLog(string $level, string $message, array $context, bool $prefixDate = true): string
    {
        $lines = \preg_split('/\n/', $message);
        $lines = \array_map('trim', $lines);
        $date = \date('[Y-m-d H:i:s] ');

        return $date . \implode(\PHP_EOL . $date, $lines) . \PHP_EOL;
    }
}
