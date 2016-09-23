<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 17:38
 */
namespace samsonframework\bitbucket;

use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Reporter interface.
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
interface ReporterInterface
{
    public function report(CloudReporter $bitbucket, ConsoleLogger $logger);
}
