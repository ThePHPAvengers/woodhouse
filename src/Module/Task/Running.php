<?php
namespace Woodhouse\Module\Task;

use Woodhouse\Module\Module;
use Woodhouse\Module\Modules;
use Woodhouse\Module\Planner\ModulePlannerBuilder;
use Woodhouse\Module\Planner\ModulesPlannerBuilder;
use Woodhouse\Module\Planner\PlannerAdapter;
use Woodhouse\Task\ITask;
use Woodhouse\Task\Task;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Running
 *
 * @package Woodhouse\Module\Task
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class Running extends Task
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if($input->getArgument('name')) {
            if(!$this->getService('module_manager')->has($input->getArgument('name'))) {
                $output->writeln(sprintf('<error>Module "%s" not found!</error>', $input->getArgument('name')));
                return ITask::BLOCKING_ERROR_CODE;
            }
            return $this->runModule($input, $output, $this->getService('module_manager')->get($input->getArgument('name')));
        }
        return $this->runModules($input, $output, $this->getService('module_manager')->getAll());
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Modules         $modules
     * @return int|null
     */
    protected function runModules(InputInterface $input, OutputInterface $output, Modules $modules)
    {
        $modules = $this->filter($modules);
        $output->writeln(sprintf('<info>Running %d module(s)</info>', count($modules)));
        $planner = new PlannerAdapter(
            new ModulesPlannerBuilder($this->getServices(), $modules, $this->getService('helper_set')->get('question')),
            $this->getService('helper_set')->get('question')
        );
        return $planner->execute($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Module          $module
     * @return int|null
     */
    private function runModule(InputInterface $input, OutputInterface $output, Module $module)
    {
        $output->writeln('<info>Running the module "'.$module->getName().'"</info>');
        $planner = new PlannerAdapter(
            new ModulePlannerBuilder($this->getServices(), $module),
            $this->getService('helper_set')->get('question')
        );
        return $planner->execute($input, $output);
    }

    /**
     * @param Modules $modules
     * @return Modules
     */
    private function filter(Modules $modules)
    {
        return new Modules(
            array_filter(
                $modules->getArrayCopy(), function (Module $module) {
                    return $module->isEnable();
                }
            )
        );
    }
}
