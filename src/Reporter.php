<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 17:45
 */
namespace samsonframework\bitbucket;

/**
 * Class Reporter
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
abstract class Reporter
{
    /** @var string Reporter source path */
    protected $path;

    /**
     * Reporter constructor.
     *
     * @param string $path Reporter source path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
