<?php declare(strict_types = 1);
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
                    new InputOption('repo', 'r', InputOption::VALUE_REQUIRED),
                    new InputOption('account', 'a', InputOption::VALUE_REQUIRED),
                    new InputOption('pull', 'p', InputOption::VALUE_REQUIRED),
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);

        $reporter = new CloudReporter(
            new \Bitbucket\API\Authentication\Basic('info@samsonos.com', 'Vovan2912~'),
            $logger,
            $input->getOption('account'),
            $input->getOption('repo'),
            (int)$input->getOption('pull')
        );
    }
}
