<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-doctrine for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-doctrine/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-doctrine/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Doctrine\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Doctrine\Admin\Model\DoctrineRestServiceModelFactory;
use Laminas\ApiTools\Doctrine\Admin\Model\DoctrineRestServiceResource;
use Laminas\ApiTools\Doctrine\Admin\Model\DoctrineRestServiceResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Prophecy\Prophecy\ProphecyInterface;

class DoctrineRestServiceResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProphecyInterface|ContainerInterface
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function missingDependencies()
    {
        return [
            'all' => [[
                DoctrineRestServiceModelFactory::class => false,
                InputFilterModel::class => false,
                DocumentationModel::class => false,
            ]],
            'DoctrineRestServiceModelFactory' => [[
                DoctrineRestServiceModelFactory::class => false,
                InputFilterModel::class => true,
                DocumentationModel::class => true,
            ]],
            'InputFilterModel' => [[
                DoctrineRestServiceModelFactory::class => true,
                InputFilterModel::class => false,
                DocumentationModel::class => true,
            ]],
            'DocumentationModel' => [[
                DoctrineRestServiceModelFactory::class => true,
                InputFilterModel::class => true,
                DocumentationModel::class => false,
            ]],
        ];
    }

    /**
     * @dataProvider missingDependencies
     *
     * @var array $dependencies
     */
    public function testFactoryRaisesExceptionIfDependenciesAreMissing($dependencies)
    {
        $factory = new DoctrineRestServiceResourceFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->setExpectedException(ServiceNotCreatedException::class, 'missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDoctrineRestServiceResource()
    {
        $factory            = new DoctrineRestServiceResourceFactory();
        $restFactory        = $this->prophesize(DoctrineRestServiceModelFactory::class)->reveal();
        $inputFilterModel   = $this->prophesize(InputFilterModel::class)->reveal();
        $documentationModel = $this->prophesize(DocumentationModel::class)->reveal();

        $this->container->has(DoctrineRestServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);
        $this->container->has(DocumentationModel::class)->willReturn(true);

        $this->container->get(DoctrineRestServiceModelFactory::class)->willReturn($restFactory);
        $this->container->get(InputFilterModel::class)->willReturn($inputFilterModel);
        $this->container->get(DocumentationModel::class)->willReturn($documentationModel);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(DoctrineRestServiceResource::class, $resource);
        $this->assertAttributeSame($restFactory, 'restFactory', $resource);
        $this->assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        $this->assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
