<?php
namespace Woodhouse\Module;

use Balloon\Factory\BalloonFactory;
use Woodhouse\Project\Composer\Composer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ModuleProcedure
 *
 * @package Woodhouse\Module
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class ModuleProcedure
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var BalloonFactory
     */
    private $balloonFactory;

    /**
     * @var ModulesSorter
     */
    private $modulesSorter;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param ModuleManager  $moduleManager
     * @param Composer       $composer
     * @param BalloonFactory $balloonFactory
     * @param ModulesSorter  $modulesSorter
     */
    public function __construct(
        ModuleManager $moduleManager,
        Composer $composer,
        BalloonFactory $balloonFactory,
        ModulesSorter $modulesSorter
    ) {
    
        $this->moduleManager = $moduleManager;
        $this->composer = $composer;
        $this->balloonFactory = $balloonFactory;
        $this->modulesSorter = $modulesSorter;
    }

    /**
     * Getter of $output
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Setter of $output
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Module $module
     * @param bool   $mustSortModules
     * @return bool
     */
    public function import(Module $module, $mustSortModules = true)
    {
        $this->log(sprintf('<info>Starting installation of %s</info>', $module->getPackage()));

        if (!$this->importModule($module)) {
            $this->log(sprintf('<error>An error occurred during the installation of %s</error>', $module->getPackage()));
            return false;
        }

        if(!$this->installModule($module, $mustSortModules)) {
            $this->rollbackImport($module);
            return false;
        }

        return true;
    }

    /**
     * @param Module $module
     * @param bool   $mustSortModules
     * @return bool
     */
    public function update(Module $module, $mustSortModules = true)
    {
        $this->log(sprintf('<info>Updating %s</info>', $module->getPackage()));

        if (!$this->updateModule($module)) {
            $this->log(sprintf('<error>An error occurred during the update of %s</error>', $module->getPackage()));
            return false;
        }

        if(!$this->installModule($module, $mustSortModules)) { //todo if dependencies are removed, we need to clean them.
            $this->log(sprintf('<error>An error occurred. You need to clean your modules. Try to remove "%s" and re-install it.</error>', $module->getPackage()));
            return false;
        }

        return true;
    }

    /**
     * @param Module $module
     * @param bool   $mustSortModules
     * @return bool
     */
    public function remove(Module $module, $mustSortModules = true)
    {
        $this->log(sprintf('<info>Removing %s</info>', $module->getPackage()));

        $dependents = $module->retrieveDependents($this->moduleManager->getAll());
        if($dependents->count()) {
            $this->log(
                sprintf(
                    '<info>The module "%s" cant not be removed because is a dependency of "%s". First remove "%s".</info>',
                    $module->getName(),
                    implode(', ', array_keys($dependents->getArrayCopy())),
                    implode(', ', array_keys($dependents->getArrayCopy()))
                )
            );
            return false;
        }

        if (!$this->removeModule($module)) {
            $this->log(sprintf('<error>An error occurred during the remove of %s</error>', $module->getPackage()));
            return false;
        }

        $this->moduleManager->remove($module->getName());

        if($mustSortModules) {
            $this->sort();
        }

        return true;
    }

    /**
     * @param Module $module
     */
    private function rollbackImport(Module $module)
    {
        $this->log(sprintf('<info>Roll-backing installation of %s</info>', $module->getPackage()));
        $this->removeModule($module);
        $this->moduleManager->clear();
    }

    /**
     * @param Module $module
     * @param bool   $mustSortModules
     * @return bool
     */
    private function installModule(Module $module, $mustSortModules)
    {
        $module = $this->completeModuleParams($module, $this->retrieveOriginalModuleData($module));
        $this->moduleManager->add($module);

        if (!$this->importDependencies($module->getDependencies())) {
            return false;
        }

        if($mustSortModules) {
            $this->sort();
        }

        return true;
    }

    /**
     * @param Modules $dependencies
     * @return bool
     */
    private function importDependencies(Modules $dependencies)
    {
        foreach ($dependencies as $dependency) {
            if (!count($this->moduleManager->getByPackage($dependency->getPackage()))) {
                if (!$this->import($dependency, false)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param Module $module
     * @return bool
     */
    private function importModule(Module $module)
    {
        if($module->getSource()) {
            throw new \RuntimeException('Sorry, for now Woodhouse does not manage a custom module source...');
        }
        return $this->composer->requirePackage($module->getPackage(), $module->getVersion(), true) === 0;
    }

    /**
     * @param Module $module
     * @return bool
     */
    private function updateModule(Module $module)
    {
        return $this->composer->updatePackage($module->getPackage(), true) === 0;
    }

    /**
     * @param Module $module
     * @return bool
     */
    private function removeModule(Module $module)
    {
        return $this->composer->removePackage($module->getPackage(), true) === 0;
    }

    /**
     * @param Module $module
     * @return array
     */
    private function retrieveOriginalModuleData(Module $module)
    {
        return $this->balloonFactory->create($this->buildConfigPath($module))->getAll();
    }

    /**
     * @param Module $module
     * @param array  $moduleData
     * @return Module
     */
    private function completeModuleParams(Module $module, array $moduleData)
    {
        if (!$module->getDescription() && !empty($moduleData['description'])) {
            $module->setDescription($moduleData['description']);
        }
        if (!$module->getSource() && !empty($moduleData['source'])) {
            $module->setDescription($moduleData['source']);
        }
        if (!empty($moduleData['dependencies'])) {
            $module->setDependencies($moduleData['dependencies']);
        }
        if (!empty($moduleData['tasks'])) {
            $module->setTasks((array)$moduleData['tasks']);
        }
        return $module;
    }

    /**
     * @param Module $module
     * @return string
     */
    private function buildConfigPath(Module $module)
    {
        return $this->composer->getHomePath()
            . '/vendor/'
            . $module->getPackage()
            . '/.Woodhouse.json';
    }

    /**
     *
     */
    private function sort()
    {
        $this->log('<info>Sorting modules</info>');
        $this->moduleManager->set($this->modulesSorter->sort($this->moduleManager->getAll()));
    }

    /**
     * @param string $message
     */
    private function log($message)
    {
        if($this->getOutput()) {
            $this->getOutput()->writeln($message);
        }
    }
}
