<?php declare(strict_types=1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 14:14
 */
namespace samsonframework\bitbucket;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Class ReporterCommand
 *
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class ReporterCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('reporter')
            ->setDescription('Report data to BitBucket pull request')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('username', 'u', InputOption::VALUE_REQUIRED, 'BitBucket account user name'),
                    new InputOption('password', 'pwd', InputOption::VALUE_REQUIRED, ' BitBucket account password'),
                    new InputOption('repo', 'r', InputOption::VALUE_REQUIRED, 'BitBucket repository name'),
                    new InputOption('account', 'a', InputOption::VALUE_REQUIRED, 'BitBucket account name'),
                    new InputOption('pull', 'p', InputOption::VALUE_REQUIRED, 'BiBucket pull request id'),
                    new InputOption('md', 'md', InputOption::VALUE_REQUIRED, 'PHP Mess Detector reporter xml path'),
                    new InputOption('screenshots', 's', InputOption::VALUE_REQUIRED, 'ScreenShots reporter folder path'),
                    //new InputOption('cpd', 'cpd', InputOption::VALUE_REQUIRED),
                    //new InputOption('jshint', 'js', InputOption::VALUE_REQUIRED),
                    //new InputOption('lesshint', 'less', InputOption::VALUE_REQUIRED),
                    //new InputOption('csshint', 'css', InputOption::VALUE_REQUIRED),
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);

        $reporter = new CloudReporter(
            new \Bitbucket\API\Authentication\Basic($input->getOption('username'), $input->getOption('password')),
            $logger,
            $input->getOption('account'),
            $input->getOption('repo'),
            (int)$input->getOption('pull')
        );

        if (null !== ($argument = $input->getOption('md'))) {
            $reporter->addReporter(new MessDetectorReporter($argument));
        }

        if (null !== ($argument = $input->getOption('screenshots'))) {
            $reporter->addReporter(new ScreenshotReporter($argument));
        }

        $reporter->report();
    }
}
