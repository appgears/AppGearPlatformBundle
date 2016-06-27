<?php

namespace AppGear\PlatformBundle\Command\Storage\MySql;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


/**
 * Class SchemaUpdateCommand предназначен для обновлении структуры MySQL в соответствии со структурой моделей
 */
class SchemaUpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('appgear:platform:storage:mysql:schema-update')
            ->setDescription('Update the database schema to match the current models')
            ->addArgument('id', InputArgument::OPTIONAL, 'Model ID')
            ->addOption('execute', null, InputOption::VALUE_NONE, 'If set, the update queries will be execute')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemeGenerator = $this->getContainer()->get('ag.service.entity.model.generate_mysql_schema');

        $storage = $this->getContainer()->get('ag.storage');

        if ($modelId = $input->getArgument('id')) {
            $models = array($models = $storage->findById('AppGear\\PlatformBundle\\Entity\\Model', $modelId));
        } else {
            $models = $storage->find('AppGear\\PlatformBundle\\Entity\\Model');
        }

        $i = 0;
        foreach ($models as $model) {
            if ($model->getName() === 'AppGear') {
                continue;
            }
            foreach($schemeGenerator->generate($model) as $query) {
                $output->writeln('# ' . $i++);
                $output->writeln($query . ';');

                if ($input->getOption('execute')) {
                    $output->write('Execute... ');
                    $this->getContainer()
                        ->get('doctrine.dbal.default_connection')
                        ->executeQuery($query);
                    $output->writeln('Done!');
                }
            }
        }

        $output->writeln('Complete!', true);
    }
}