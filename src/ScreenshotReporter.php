<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 15:26
 */
namespace samsonframework\bitbucket;

use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Screenshot reporter.
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class ScreenshotReporter extends Reporter implements ViolationReporterInterface
{
    /** Violations collection marker */
    const MARKER = 'screenshots';

    /** {@inheritdoc} */
    public function parseViolations() : array
    {
        /** @var array $violations Collection of violations grouped by files and lines */
        $violations = [];

        // Collection screenshots
        foreach (glob($this->path.'/*.jpg') as $image) {
            $violations[] = $image;
        }

        return $violations;
    }

    /** {@inheritdoc} */
    public function report(CloudReporter $bitbucket, ConsoleLogger $logger)
    {
        foreach ($this->parseViolations() as $screenshot) {
            $bitbucket->createGeneralComment('![Screent shot](' . $screenshot . ')');
            $logger->log(ConsoleLogger::INFO, 'Posting screenshot:' . $screenshot);
        }
    }
}
