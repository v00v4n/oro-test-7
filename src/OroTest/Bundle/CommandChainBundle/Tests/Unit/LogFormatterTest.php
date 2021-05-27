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
namespace OroTest\Bundle\CommandChainBundle\Tests\Unit;

use JetBrains\PhpStorm\ArrayShape;
use OroTest\Bundle\CommandChainBundle\CommandChain\LogFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Class LogFormatterTest
 *
 * @package OroTest\Bundle\CommandChainBundle\Tests\Unit
 * @covers \OroTest\Bundle\CommandChainBundle\CommandChain\LogFormatter
 */
class LogFormatterTest extends TestCase
{
    /**
     * @dataProvider logFormatDataProvider
     */
    public function testFormatLog(string $message, string $expected)
    {
        $currentDateTime = date(LogFormatter::DATETIME_FORMAT);
        $actual = (new LogFormatter)->formatLog('', $message, []);

        $dateTimes = [];
        $actual = preg_replace_callback('/^\[([^\]]+)\] /m', function ($m) use (&$dateTimes) {
            $dateTimes[] = $m[1];

            return '[] ';
        }, $actual);

        // check format
        $this->assertSame($expected, $actual);

        // check date/times is same
        $lastDateTime = null;
        foreach ($dateTimes as $datetime) {
            if ($lastDateTime) {
                $this->assertSame($lastDateTime, $datetime);
            }
            $lastDateTime = $datetime;
        }

        $this->assertSame($lastDateTime, $currentDateTime);
    }

    #[ArrayShape(['inline' => "string[]", 'multiline' => "string[]"])]
    public function logFormatDataProvider()
    {
        $eol = \PHP_EOL;

        return [
            'inline' => [
                'inline message',
                "[] inline message$eol",
            ],
            'multiline' => [
                "multiline\r\nmessage",
                "[] multiline{$eol}[] message$eol"
            ],
        ];
    }
}
