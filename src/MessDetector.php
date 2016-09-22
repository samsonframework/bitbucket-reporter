<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 15:26
 */
namespace samsonframework\bitbucket;

/**
 * Class MessDetector
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class MessDetector
{
    /** XML File path field */
    const FILEPATH = 'name';

    /** XML File line number  */
    const LINENUMBER = 'beginline';

    /** @var array XML PHP mess detector data */
    protected $xmlData = [];

    /**
     * Convert xml to array
     *
     * @param $xmlObject
     * @param array $out
     * @return array
     */
    protected function xml2array($xmlObject, array $out = []): array
    {
        foreach ((array)$xmlObject as $index => $node) {
            $out[$index] = (is_object($node) || is_array($node)) ? $this->xml2array($node) : $node;
        }
        return $out;
    }

    /**
     * MessDetector constructor.
     *
     * @param string $xmlPath Path to mess detector xml file
     */
    public function __construct(string $xmlPath)
    {
        // Read XML and convert to array
        $this->xmlData = simplexml_load_string(file_get_contents($xmlPath));
    }

    /**
     * Parse XML data and return collection: file => line => violation.
     *
     * @param string $basePath Bse path to code for matching BitBucket paths
     *
     * @return array Collection: file => line => violation.
     */
    public function getViolations($basePath = '/src')
    {
        /** @var array $violations Collection of violations grouped by files and lines */
        $violations = [];

        foreach ($this->xmlData->file as $file) {
            $filePath = (string)$file[self::FILEPATH];
            $pointer = &$violations[ltrim(substr($filePath, strpos($filePath, $basePath)), '/')];
            foreach ($file->violation as $violation) {
                $pointer[(string)$violation[self::LINENUMBER]][] = trim((string)$violation);
            }
        }

        return $violations;
    }
}
