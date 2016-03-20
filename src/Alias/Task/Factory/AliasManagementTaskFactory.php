<?php
namespace Woodhouse\Alias\Task\Factory;

use Pimple\Container;
use Woodhouse\Alias\AliasCommand;
use Woodhouse\Alias\Task\Listing;
use Woodhouse\Alias\Task\Removing;
use Woodhouse\Alias\Task\Saving;
use Woodhouse\Task\ITask;
use SimilarText\Finder;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class TaskFactory
 *
 * @package Woodhouse\Alias\Task\Factory
 * @author  Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class AliasManagementTaskFactory
{
    /**
     * @param InputInterface $input
     * @param Container      $services
     * @return ITask
     */
    public function create(InputInterface $input, Container $services)
    {
        if(!$input->getArgument('action')) {
            throw new \InvalidArgumentException(sprintf('An action param is required: %s', json_encode(AliasCommand::$actions)));
        }

        if($input->getArgument('action') === 'list') {
            return new Listing($services);
        }
        if($input->getArgument('action') === 'save') {
            if(!$input->getArgument('name')) {
                throw new \InvalidArgumentException('name param is mandatory for this action');
            }
            if(!$input->getArgument('bootstrap')) {
                throw new \InvalidArgumentException('bootstrap param is mandatory for this action');
            }
            return new Saving($services);
        }
        if($input->getArgument('action') === 'rm' || $input->getArgument('action') === 'remove') {
            if(!$input->getArgument('name')) {
                throw new \InvalidArgumentException('name param is mandatory for this action');
            }
            return new Removing($services);
        }

        $textFinder = new Finder($input->getArgument('action'), AliasCommand::$actions);
        throw new \InvalidArgumentException(
            sprintf(
                'Action "%s" not supported. Did you mean "%s"?',
                $input->getArgument('action'),
                $textFinder->first()
            )
        );
    }
}
