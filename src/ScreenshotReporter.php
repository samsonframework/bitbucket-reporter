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
    /** {@inheritdoc} */
    public function parseViolations() : array
    {

        // Read XML and convert to array
        $xmlData = simplexml_load_string(file_get_contents($this->path));

        /** @var array $violations Collection of violations grouped by files and lines */
        $violations = [];

        foreach ($xmlData->file as $file) {
            $filePath = (string)$file[self::FILEPATH];
            $pointer = &$violations[ltrim(substr($filePath, strpos($filePath, $this->basePath)), '/')];
            foreach ($file->violation as $violation) {
                $pointer[(string)$violation[self::LINENUMBER]][] = trim((string)$violation);
            }
        }

        return $violations;
    }
}
