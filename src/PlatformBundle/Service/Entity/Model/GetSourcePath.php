<?php

namespace AppGear\PlatformBundle\Service\Entity\Model;


class GetSourcePath
{
    /**
     * PHP namespace separator
     */
    const NAMESPACE_SEPARATOR = '\\';

    /**
     * Class loader map
     *
     * @var array
     */
    protected $classLoaderMap;

    /**
     * Kernel Root Directory
     *
     * @var string
     */
    protected $kernelRootDir;

    /**
     * Constructor
     *
     * @param array  $classLoaderMap Class Loader Map
     * @param string $kernelRootDir  Kernel Root Directory
     */
    public function __construct($classLoaderMap, $kernelRootDir)
    {
        $this->classLoaderMap = $classLoaderMap;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Возвращает путь к файлу с исходным кодом для модели
     *
     * @param $model Модель
     *
     * @return string
     */
    public function get($model)
    {
        $scopes = $model->getScope()->getParentOrSelfNames();
        $classPrefix = $this->findClassPrefix($scopes);
        $prefixDir = $this->classLoaderMap[$classPrefix][0];
        $leftScopes = array_slice($scopes, substr_count($classPrefix, self::NAMESPACE_SEPARATOR));

        return $prefixDir
            . DIRECTORY_SEPARATOR
            . implode(DIRECTORY_SEPARATOR, $leftScopes)
            . DIRECTORY_SEPARATOR
            . $model->getName()
            . '.php';
    }


    /**
     * Find suitable classLoader prefix for the model scopes
     *
     * @param string[] $modelScopes Model scopes
     *
     * @return string
     */
    protected function findClassPrefix($modelScopes)
    {
        $classLoaderMapPrefixes = array_keys($this->classLoaderMap);
        $namespacePrefix = '';

        do {
            $namespacePrefix .= array_shift($modelScopes) . self::NAMESPACE_SEPARATOR;

            if (array_search($namespacePrefix, $classLoaderMapPrefixes)) {
                return $namespacePrefix;
            }

            $classLoaderMapPrefixes = array_filter($classLoaderMapPrefixes, function ($key) use ($namespacePrefix) {
                return (strpos($key, $namespacePrefix) === 0);
            });
        } while (count($modelScopes) > 0 && count($classLoaderMapPrefixes) > 0);

        return '';
    }
}
