<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 09:26
 */
namespace samsonframework\bitbucket;

use Bitbucket\API\Authentication\AuthenticationInterface;
use Bitbucket\API\Repositories\Changesets;
use Bitbucket\API\Repositories\PullRequests;
use Buzz\Message\MessageInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Class BitBucketCloudReporter.
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class CloudReporter
{
    /** @var PullRequests */
    protected $client;

    /** @var Changesets */
    protected $changeSets;

    /** @var string BitBucket account name */
    protected $accountName;

    /** @var string BitBucket repository name */
    protected $repoName;

    /** @var int BitBucket pull request id */
    protected $pullRequestId;

    /** @var ConsoleLogger */
    protected $logger;

    public function __construct(
        AuthenticationInterface $credentials,
        ConsoleLogger $logger,
        MessDetector $detector,
        string $accountName,
        string $repoName,
        int $pullRequestId
    ) {
        $this->accountName = $accountName;
        $this->repoName = $repoName;
        $this->pullRequestId = $pullRequestId;
        $this->logger = $logger;

        $this->client = new PullRequests();
        $this->client->setCredentials($credentials);

        $this->changesets = new Changesets();
        $this->changesets->setCredentials($credentials);

        // Read PHP Mess detector violations
        $messDetectorViolations = $detector->getViolations();

        // Iterate only files changed by pull request
        foreach ($this->getChangedFiles() as $file) {
            // Check if we have PMD violations in that files
            if (array_key_exists($file, $messDetectorViolations)) {
                // Iterate file violations
                foreach ($messDetectorViolations[$file] as $line => $violations) {
                    // Iterate file line violations
                    foreach ($violations as $violation) {
                        // Send comment to BitBucket pull request
                        $this->createComment($violation, $file, $line);
                    }
                }
            }
        }
    }

    /**
     * Collection of changed files in pull request.
     *
     * @return string[] Collection of changed files
     */
    public function getChangedFiles()
    {
        $files = [];
        $responseString = $this->client->commits($this->accountName, $this->repoName, $this->pullRequestId);

        try {
            $responseObject = json_decode($responseString->getContent());

            if (isset($responseObject->error)) {
                $this->logger->critical($responseObject->error->message);
            } elseif (isset($responseObject->values) && is_array($responseObject->values)) {
                foreach ($responseObject->values as $commit) {
                    $changeSet = $this->changesets->diffstat($this->accountName, $this->repoName, $commit->hash);
                    $files[] = json_decode($changeSet->getContent())[0]->file;
                }
            } else {
                $this->logger->log(ConsoleLogger::INFO, 'BitBucket response has no values');
            }
        } catch (\InvalidArgumentException $exception) {
            $this->logger->critical('Cannot json_decode BitBucket response');
        }

        return $files;
    }

    /**
     * Add a new comment to pull request.
     *
     * @param  string     $content  The comment
     * @param null|string $filename File name
     * @param int|null    $lineFrom Source code line number
     *
     * @return MessageInterface
     */
    public function createComment(string $content, string $filename = null, int $lineFrom = null) : MessageInterface
    {
        return $this->client->requestPost(
            sprintf('repositories/%s/%s/pullrequests/%d/comments', $this->accountName, $this->repoName, $this->pullRequestId),
            [
                'content' => $content,
                'filename' => $filename,
                'line_from' => $lineFrom
            ]
        );
    }
}


