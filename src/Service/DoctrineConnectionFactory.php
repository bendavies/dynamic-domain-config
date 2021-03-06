<?php

namespace DynamicDomainConfig\Service;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DoctrineConnectionFactory extends ConnectionFactory implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $doctrineConnectionMapping;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param array $mapping
     */
    public function setDoctrineConnectionMapping(array $mapping)
    {
        $this->doctrineConnectionMapping = $mapping;
    }

    /**
     * @param array $params
     * @param Configuration|null $config
     * @param EventManager|null $eventManager
     * @param array $mappingTypes
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ) {
        /** @var DomainConfigResolver $resolver */
        $resolver = $this->container->get('domain.config.resolver');
        if (!$resolver->isExistServerName()) {
            return parent::createConnection($params, $config, $eventManager,$mappingTypes);
        }

        $parameters = $resolver->getCurrentParams();

        foreach ($this->doctrineConnectionMapping as $key => $value) {
            if (isset($parameters[$value])) {
                $params[$key] = $parameters[$value];
            }
        }

        return parent::createConnection($params, $config, $eventManager,$mappingTypes);
    }
}

