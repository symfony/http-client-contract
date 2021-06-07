<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\HttpClient\Test;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class TestHttpServer
{
    private static $process = [];

    public static function start(int $port = 8057)
    {
        if (isset(self::$process[$port])) {
            self::$process[$port]->stop();
        } else {
            register_shutdown_function(static function () use ($port) {
                self::$process[$port]->stop();
            });
        }

        $localhost = gethostbyname('localhost');
        $finder = new PhpExecutableFinder();
        $process = new Process(array_merge([$finder->find(false)], $finder->findArguments(), ['-dopcache.enable=0', '-dvariables_order=EGPCS', '-S', "$localhost:$port"]));
        $process->setWorkingDirectory(__DIR__.'/Fixtures/web');
        $process->start();
        self::$process[$port] = $process;

        do {
            usleep(50000);
        } while (!@fopen("http://$localhost:$port", 'r'));

        return $process;
    }
}
