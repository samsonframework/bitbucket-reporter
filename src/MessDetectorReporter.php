<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 15:26
 */
namespace samsonframework\bitbucket;

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
}
