<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 15:26
 */
namespace samsonframework\bitbucket;

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
            $violations[self::MARKER][0][] = $image;
        }

        return $violations;
    }
}
