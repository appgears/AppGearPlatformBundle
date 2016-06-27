<?php

namespace AppGear\PlatformBundle\Service\Entity\View;

use AppGear\PlatformBundle\Entity\View;
use Cosmologist\Gears\Obj\PropertyRecursiveAccess;
use Symfony\Component\Templating\EngineInterface;

class FindTemplate
{
    /**
     * Find suitable template for the model
     *
     * @param EngineInterface $templateEngine Template engine
     * @param Model $model Model
     * @param array $nameParts Additional name parts
     * @param array $prefixParts Additional prefix parts
     *
     * @return null|string
     */
    public function find(EngineInterface $templateEngine, $model, $nameParts = [], $prefixParts = [])
    {
        $models = PropertyRecursiveAccess::get($model, 'parent', true);

        foreach ($models as $model) {
            $path = $this->buildPath($model, $nameParts, $prefixParts);

            if ($templateEngine->exists($path)) {
                return $path;
            }
        }

        return null;
    }


    /**
     * Build template path for the model
     *
     * @param Model $model Model
     * @param array $nameParts Additional name parts
     * @param array $prefixParts Additional prefix parts
     *
     * @return string
     */
    public function buildPath($model, $nameParts = [], $prefixParts = [])
    {
        $parts = explode('\\', $model->getFullName());

        // Bundle prefix
        $path = $parts[0] . $parts[1];

        $parts = array_slice($parts, 3);

        if (count($prefixParts) > 0) {
            $parts = array_merge($prefixParts, $parts);
        }
        if (count($nameParts) > 0) {
            $parts = array_merge($parts, $nameParts);
        }

        if (count($parts) === 1) {
            $path .= '::' . $parts[0];
        } else {
            foreach ($parts as $i => $part) {
                $separator = ($i < 2) ? ':' : DIRECTORY_SEPARATOR;
                $path .= $separator . $part;
            }
        }

        return $path . '.html.twig';
    }
}