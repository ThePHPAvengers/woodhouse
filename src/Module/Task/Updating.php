<?php
namespace Woodhouse\Module\Task;

use Woodhouse\Task\ITask;
use Woodhouse\Task\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Updating
 *
 * @package Woodhouse\Module\Task
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class Updating extends Task
{

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if($input->getArgument('name')) {
            return $this->updateModule($input, $output);
        }
        return $this->updateModules($output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    private function updateModule(InputInterface $input, OutputInterface $output)
    {
        $moduleName = $input->getArgument('name');
        if (!$this->getService('module_manager')->has($moduleName)) {
            $output->writeln(sprintf('<error>Error: no module "%s" found</error>', $moduleName));
            return ITask::BLOCKING_ERROR_CODE;
        }

        $this->getService('module_procedure')->setOutput($output);
        $this->getService('module_procedure')->update($this->getService('module_manager')->get($moduleName));
        return ITask::NO_ERROR_CODE;
    }

    /**
     * @param OutputInterface $output
     * @return int
     */
    private function updateModules(OutputInterface $output)
    {
        $hasError = false;
        $this->getService('module_procedure')->setOutput($output);
        foreach($this->getService('module_manager')->getAll() as $module){
            if($this->getService('module_procedure')->update($module)) {
                $hasError = true;
            }
        }
        return $hasError ? ITask::NO_ERROR_CODE : ITask::NON_BLOCKING_ERROR_CODE;
    }
}
