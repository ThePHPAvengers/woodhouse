<?php
namespace Woodhouse\Project\Question;

use Woodhouse\alias\Alias;
use Woodhouse\Task\ITask;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class BootstrapQuestion
 *
 * @package Woodhouse\Project\Question
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class BootstrapQuestion extends Question
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if($input->getArgument('bootstrap')) {
            $this->setFromArgs($input);
        }else{
            $this->setFromAlias($this->askForAlias($input, $output));
        }
        return $this->getProject()->getBootstrap()->getPackage() ? ITask::NO_ERROR_CODE : ITask::BLOCKING_ERROR_CODE;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return Alias
     */
    private function askForAlias(InputInterface $input, OutputInterface $output)
    {
        return $this->ask(
            $input,
            $output,
            new ChoiceQuestion(
                '<question>Choose a bootstrap:</question>',
                $this->getService('alias_manager')->getAll()
            )
        );
    }

    /**
     * @param Alias $alias
     */
    private function setFromAlias(Alias $alias)
    {
        $this->getProject()->setBootstrap($alias);
    }

    /**
     * @param InputInterface $input
     */
    private function setFromArgs(InputInterface $input)
    {
        if ($this->getService('alias_manager')->has($input->getArgument('bootstrap'))) {
            $this->setFromAlias($this->getService('alias_manager')->get($input->getArgument('bootstrap')));
        } else {
            $alias = new Alias();
            $alias->setPackage($input->getArgument('bootstrap'));
            $alias->setVersion($input->getArgument('version'));
            $alias->setSource($input->getArgument('source'));
            $this->setFromAlias($alias);
        }
    }
}
