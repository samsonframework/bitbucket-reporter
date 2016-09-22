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
    protected $pullRequests;

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

    /** @var string Pull request author */
    protected $author;

    /** @var ReporterInterface[] */
    protected $reporters = [];

    public function __construct(
        AuthenticationInterface $credentials,
        ConsoleLogger $logger,
        ReporterInterface $detector,
        string $accountName,
        string $repoName,
        int $pullRequestId
    ) {
        $this->accountName = trim($accountName);
        $this->repoName = trim($repoName);
        $this->pullRequestId = $pullRequestId;
        $this->logger = $logger;

        $this->pullRequests = new PullRequests();
        $this->pullRequests->setCredentials($credentials);

        $this->changesets = new Changesets();
        $this->changesets->setCredentials(clone $credentials);

        $this->author = $this->getPullRequestAuthor();



        // Iterate file violations
//        foreach ($messDetectorViolations as $file => $lines) {
//            foreach ($lines as $line => $violations) {
//                // Iterate file line violations
//                foreach ($violations as $violation) {
//                    // Send comment to BitBucket pull request
//                    $this->createComment($violation, $file, $line);
//                }
//            }
//        }


    }

    /**
     * Add reporter.
     *
     * @param ReporterInterface $reporter Reporter instance
     */
    public function addReporter(ReporterInterface $reporter)
    {
        $this->reporters[] = $reporter;
    }

    public function report()
    {
        // Gather all violations
        $violations = [];
        foreach ($this->reporters as $reporter) {
            if ($reporter instanceof ViolationReporterInterface) {
                /** @var ViolationReporterInterface $reporter */
                $violations = array_merge($violations, $reporter->parseViolations());
            }
        }

        $this->logger->log(ConsoleLogger::INFO, 'Found '.count($violations).' files with violations');

        // Iterate only files changed by pull request
        foreach ($this->getChangedFiles() as $file) {
            // Check if we have PMD violations in that files
            if (array_key_exists($file, $violations)) {
                // Iterate file violations
                foreach ($violations[$file] as $line => $violations) {
                    // Iterate file line violations
                    foreach ($violations as $violation) {
                        // Send comment to BitBucket pull request
                        $this->createFileComment($violation, $file, $line);
                    }
                }
            }
        }
    }

    /**
     * Get pull request author username.
     *
     * @return string Pull request author username
     */
    public function getPullRequestAuthor()
    {
        $responseString = $this->pullRequests->get($this->accountName, $this->repoName, $this->pullRequestId);
        try {
            $responseObject = json_decode($responseString->getContent());

            if (isset($responseObject->error)) {
                $this->logger->critical($responseObject->error->message);
            } elseif (isset($responseObject->author)) {
                return $responseObject->author->username;
            } else {
                $this->logger->log(ConsoleLogger::INFO, 'BitBucket response has no values');
            }
        } catch (\InvalidArgumentException $exception) {
            $this->logger->critical('Cannot json_decode BitBucket response');
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
        $responseString = $this->pullRequests->commits($this->accountName, $this->repoName, $this->pullRequestId);

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
     * Create general pull request comment.
     *
     * @param string $content The comment content
     *
     * @return MessageInterface
     */
    public function createGeneralComment(string $content)
    {
        return $this->postComment(['content' => $content]);
    }

    /**
     * Add a new comment to pull request.
     *
     * @param string      $content  The comment content
     * @param null|string $filename File name
     * @param int|null    $lineFrom Source code line number
     *
     * @return MessageInterface
     */
    public function createFileComment(string $content, string $filename = null, int $lineFrom = null) : MessageInterface
    {
        $this->logger->log(ConsoleLogger::INFO, 'Creating comment in: '.$filename.'#'.$lineFrom.' - '.$content);

        return $this->postComment([
            'content' => $content,
            'filename' => $filename,
            'line_from' => $lineFrom
        ]);
    }

    /**
     * Low level post request for creating pull request comment.
     *
     * @param array $commentData Comment data
     *
     * @return MessageInterface
     */
    protected function postComment(array $commentData)
    {
        // Add pull request author
        $commentData['content'] = '@'.$this->author.' '.$commentData['content'];

        // Switch to old API version
        $this->pullRequests->getClient()->setApiVersion('1.0');

        return $this->pullRequests->comments()->requestPost(
            sprintf('repositories/%s/%s/pullrequests/%d/comments', $this->accountName, $this->repoName, $this->pullRequestId),
            $commentData
        );
    }
}


