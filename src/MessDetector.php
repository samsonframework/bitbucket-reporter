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

    public function __construct(string $xmlPath)
    {
        // Read XML and convert to array
        $this->xmlData = simplexml_load_string(file_get_contents($xmlPath));
    }

    /**
     * Parse XML data and return collection: file => line => violation
     * @return array
     */
    public function getViolations()
    {
        /** @var array $violations Collection of violations grouped by files and lines */
        $violations = [];

        $pointer = &$violations;
        foreach ($this->xmlData->file as $file) {
            $pointer = &$violations[(string)$file['name']];
            foreach ($file->violation as $violation) {
                $pointer[(string)$violation['beginline']] = trim((string)$violation);
            }

        }

        return $violations;
    }
}
