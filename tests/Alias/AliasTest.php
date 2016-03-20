<?php
namespace Woodhouse\Alias;

/**
 * Class AliasTest
 * @package Woodhouse\Alias
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class AliasTest extends \PHPUnit_Framework_TestCase
{

    public function testToArray()
    {
        $alias = new Alias();
        $alias->setName('name');
        $alias->setDescription('description');
        $alias->setPackage('bootstrap');
        $alias->setVersion('version');
        $alias->setSource('source');
        $this->assertSame(
            [
              'name' => 'name',
              'description' => 'description',
              'package' => 'bootstrap',
              'version' => 'version',
              'source' => 'source',
            ],
            $alias->toArray()
        );
    }
}
