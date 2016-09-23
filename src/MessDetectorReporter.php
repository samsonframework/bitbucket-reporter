<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 15:26
 */
namespace samsonframework\bitbucket;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * PHP mess detector violations reporter.
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class MessDetectorReporter extends Reporter implements ViolationReporterInterface
{
    /** XML File path field */
    const FILEPATH = 'name';

    /** XML File line number  */
    const LINENUMBER = 'beginline';

    /** @var string Base file path marker for normalization */
    protected $basePath;

    /**
     * MessDetectorReporter constructor.
     *
     * @param string $path Reporter source path
     * @param string $basePath Base file path marker for normalization
     */
    public function __construct(string $path, string $basePath = '/src')
    {
        $this->basePath = $basePath;

        parent::__construct($path);
    }

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

    /** {@inheritdoc} */
    public function report(CloudReporter $bitbucket, ConsoleLogger $logger)
    {
        $violations = $this->parseViolations();

        $logger->log(ConsoleLogger::INFO, 'Found '.count($violations, COUNT_RECURSIVE).' violations');

        // Iterate only files changed by pull request
        foreach ($bitbucket->getChangedFiles() as $file) {
            // Check if we have PMD violations in that files
            if (array_key_exists($file, $violations)) {
                // Iterate file violations
                // TODO: Check lines if they are within this changeset
                foreach ($violations[$file] as $line => $violations) {
                    // Iterate file line violations
                    foreach ($violations as $violation) {
                        // Send comment to BitBucket pull request
                        $bitbucket->createFileComment($violation, $file, $line);
                    }
                }
            }
        }
    }
}
