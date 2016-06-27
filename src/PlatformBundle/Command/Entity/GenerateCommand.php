<?php

namespace AppGear\PlatformBundle\Command\Entity;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Generate entities
 */
class GenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('appgear:platform:entity:generate')
            ->setDescription('Generate entities')
            ->addArgument('id', InputArgument::OPTIONAL, 'Model ID');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('ag.cache.manager')->deleteAll();

        $storage   = $this->getContainer()->get('ag.storage');
        $generator = $this->getContainer()->get('ag.service.entity.model.generate_source');

        if ($modelId = $input->getArgument('id')) {
            $models = array($models = $storage->findById('AppGear\\PlatformBundle\\Entity\\Model', $modelId));
        } else {
            $models = $storage->find('AppGear\\PlatformBundle\\Entity\\Model');
        }

        foreach ($models as $model) {
            $output->write('Perform "' . $model->getFullName() . '" model', true);

            $generator->generate($model);
        }

        $output->write('Complete!', true);
    }
}