<?php declare(strict_types=1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 17:35
 */
namespace samsonframework\bitbucket;

/**
 * Interface for reading and reporting violations.
 *
 * @package samsonframework\bitbucket
 * @author  Vitaly Egorov <egorov@samsonos.com>
 */
interface ViolationReporterInterface extends ReporterInterface
{
    /**
     * Parse reporter violations file and return violations collection.
     *
     * @return array Collection of [filepath => [line => [violations]]]
     */
    public function parseViolations() : array;
}
