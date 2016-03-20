<?php
namespace Woodhouse\Alias\Task;

use Woodhouse\Alias\Alias;
use Woodhouse\Task\ITask;
use Woodhouse\Task\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class Saving
 *
 * @package Woodhouse\Alias\Task
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class Saving extends Task
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return bool
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $alias = $this->buildAlias($input);
        if($this->canBeSaved($input, $output, $alias)) {
            $this->getService('alias_manager')->add($alias);
        }
        return ITask::NO_ERROR_CODE;
    }

    /**
     * @param InputInterface $input
     * @return Alias
     */
    private function buildAlias(InputInterface $input)
    {
        $alias = new Alias();
        $alias->setName($input->getArgument('name'));
        $alias->setDescription($input->getArgument('description'));
        $alias->setPackage($input->getArgument('bootstrap'));
        $alias->setVersion($input->getArgument('version'));
        $alias->setSource($input->getArgument('source'));
        return $alias;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Alias           $alias
     * @return bool
     */
    private function canBeSaved(InputInterface $input, OutputInterface $output, Alias $alias)
    {
        return !$this->getService('alias_manager')->has($alias->getName())
            || $this->confirmOverride($input, $output, $alias);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Alias           $alias
     * @return mixed
     */
    private function confirmOverride(InputInterface $input, OutputInterface $output, Alias $alias)
    {
        return $this->getService('helper_set')->get('question')->ask(
            $input,
            $output,
            $this->buildQuestion($alias, $this->getInitialAlias($alias))
        );
    }

    /**
     * @param Alias $newAlias
     * @return Alias
     */
    private function getInitialAlias(Alias $newAlias)
    {
        return $this->getService('alias_manager')->get($newAlias->getName());
    }

    /**
     * @param Alias $newAlias
     * @param Alias $oldAlias
     * @return ConfirmationQuestion
     */
    private function buildQuestion(Alias $newAlias, Alias $oldAlias)
    {
        return new ConfirmationQuestion(
            sprintf(
                '<question>Do you want to override the bootstrap "%s" with "%s" </question>[y]',
                trim($oldAlias->getPackage() . ' ' . $oldAlias->getVersion()),
                trim($newAlias->getPackage() . ' ' . $newAlias->getVersion())
            )
        );
    }
}
