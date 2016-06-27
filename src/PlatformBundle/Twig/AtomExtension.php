<?php

namespace AppGear\PlatformBundle\Twig;

use AppGear\PlatformBundle\Service\TaggedManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Расширение делает доступными в шаблонах атомы, помеченные соответствующим образом
 *
 * @package AppGear\PlatformBundle\Twig
 */
class AtomExtension extends \Twig_Extension
{
    /**
     * Service Container
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * Tagged Atoms Manager
     * @var \AppGear\PlatformBundle\Service\TaggedManager
     */
    private $taggedManager;


    /**
     * @param ContainerInterface $container
     * @param \AppGear\PlatformBundle\Service\TaggedManager $taggedManager
     */
    public function __construct(ContainerInterface $container, TaggedManager $taggedManager)
    {
        $this->container = $container;
        $this->taggedManager = $taggedManager;
    }


    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        $result = array();

        $container = $this->container;

        // Перебираем все атомы с тегом ag.twig, то есть атомы доступные в твиг-шаблонах
        foreach ($this->taggedManager->findServices('ag.twig') as $atom) {

            // Получаем имя твиг-функции на основе идентификатора сервиса
            $twigFunctionName = str_replace('.', '_', $atom['id']);

            // Создаем замыкание, которое будет вызывать атом
            $callback = function() use($container, $atom) {
                $args = func_get_args();
                $service = $container->get($atom['id']);

                return call_user_func_array(array($service, $this->getAtomMethodName($atom['id'], $atom['attributes'])), $args);
            };

            // Добавляем всё это в твиг
            $result[] =  new \Twig_SimpleFilter($twigFunctionName, $callback);
        }

        return $result;
    }

    /**
     * Получаем имя метода атома на основе его идентификатора
     *
     * @param string $id ID атома
     * @param array $attributes Атрибуты атома
     *
     * @return string Имя метода атома
     */
    protected function getAtomMethodName($id, $attributes)
    {
        // Имя метода генерируется автоматически на основе имени класса атома, но бывают случаи, когда имя должно
        // быть конкретное (к примеру, для реализации какого-либо интерфейса)
        if (array_key_exists('atom_method_name', $attributes)) {
            return $attributes['atom_method_name'];
        }

        // Если разбить имя сервиса на слова (c расчетом что каждое разделено с помощью _),
        // то имя метода атома совпадет с первым словом из имени сервиса
        $serviceIdParts = explode('.', $id);
        $serviceName = array_pop($serviceIdParts);
        $serviceNameParts = explode('_', $serviceName);

        return array_shift($serviceNameParts);
    }


    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'atom_extension';
    }
}