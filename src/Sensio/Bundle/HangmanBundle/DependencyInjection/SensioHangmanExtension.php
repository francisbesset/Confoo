<?php

namespace Sensio\Bundle\HangmanBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SensioHangmanExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // @hack
        if ('test' === $container->getParameter('kernel.environment')) {
            $config['dictionaries'] = array(__DIR__.'/../Tests/Fixtures/words.txt');
        }

        $container->setParameter('hangman.word_length', $config['word_length']);
        $container->setParameter('hangman_dictionaries', $config['dictionaries']);
    }
}
