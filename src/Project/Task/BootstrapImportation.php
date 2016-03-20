<?php
namespace Woodhouse\Project\Task;

use Woodhouse\Task\ITask;
use Woodhouse\Task\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BootstrapImportation
 *
 * @package Woodhouse\Project\Task
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class BootstrapImportation extends Task
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->getService('project')->getBootstrap()) {
            throw new \InvalidArgumentException('The bootstrap of the project is not defined');
        }

        $output->writeln(
            sprintf(
                '<info>Installing project %s from %s</info>',
                $this->getService('project')->getName(),
                $this->getService('project')->getBootstrap()->getPackage()
            )
        );

        return $this->getService('composer')->createProject($this->getService('project'), $this->getOptions()) === 0 ? ITask::NO_ERROR_CODE : ITask::BLOCKING_ERROR_CODE;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        return array_filter(
            [
            'repository-url' => $this->getService('project')->getBootstrap()->getSource()
            ]
        );
    }
}
