<?php

namespace AppGear\PlatformBundle\Twig;

use AppGear\PlatformBundle\Entity\Model\Property\Relationship;
use AppGear\PlatformBundle\Service\Entity\View\FindTemplate;
use Cosmologist\Gears\ObjectType\PropertyRecursiveAccess;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * AppGear twig extension
 */
class AppGearExtension extends Twig_Extension
{
    /**
     * Template finder
     *
     * @var FindTemplate
     */
    private $templateFinder;

    /**
     * Constructor
     *
     * @param FindTemplate $templateFinder Template finder
     */
    public function __construct(FindTemplate $templateFinder)
    {
        $this->templateFinder = $templateFinder;
    }

    /**
     * * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('class', array($this, 'getShortClassName')),
            new Twig_SimpleFilter('auto_convert_urls', array($this, 'autoConvertUrls'))
        );
    }

    /**
     * * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('render_custom_relationship_view', [$this, 'renderCustomRelationshipView'], [
                'needs_environment' => true,
                'needs_context' => true
            ]),
            new Twig_SimpleFunction('get_property_recursive', [$this, 'getPropertyRecursive'])
        ];
    }


    /**
     * Return short class name
     *
     * @param object|string $input Object or class name
     *
     * @return string
     */
    public function getShortClassName($input)
    {
        return (new \ReflectionClass($input))->getShortName();
    }


    /**
     * Method that finds different occurrences of urls or email addresses in a string.
     *
     * @see https://github.com/liip/LiipUrlAutoConverterBundle/blob/master/Extension/UrlAutoConverterTwigExtension.php
     *
     * @param string $string input string
     *
     * @return string with replaced links
     */
    public function autoConvertUrls($string)
    {
        $pattern = '/(href="|src=")?([-a-zA-Zа-яёА-ЯЁ0-9@:%_\+.~#?&\/\/=]{2,256}\.[a-zа-яё]{2,4}\b(\/?[-\p{L}0-9@:%_\+.~#?&\/\/=\(\),]*)?)/u';
        $stringFiltered = preg_replace_callback($pattern, array($this, 'callbackReplace'), $string);
        return $stringFiltered;
    }

    /**
     * @see https://github.com/liip/LiipUrlAutoConverterBundle/blob/master/Extension/UrlAutoConverterTwigExtension.php
     *
     * @param $matches
     *
     * @return string
     */
    public function callbackReplace($matches)
    {
        if ($matches[1] !== '') {
            return $matches[0]; // don't modify existing <a href="">links</a> and <img src="">
        }
        $url = $matches[2];
        $urlWithPrefix = $matches[2];
        if (strpos($url, '@') !== false) {
            $urlWithPrefix = 'mailto:' . $url;
        } elseif (strpos($url, 'https://') === 0) {
            $urlWithPrefix = $url;
        } elseif (strpos($url, 'http://') !== 0) {
            $urlWithPrefix = 'http://' . $url;
        }
        // ignore tailing special characters
        // TODO: likely this could be skipped entirely with some more tweakes to the regular expression
        if (preg_match("/^(.*)(\.|\,|\?)$/", $urlWithPrefix, $matches)) {
            $urlWithPrefix = $matches[1];
            $url = substr($url, 0, -1);
            $punctuation = $matches[2];
        } else {
            $punctuation = '';
        }
        return '<a href="' . $urlWithPrefix . '" target="_blank">' . $url . '</a>' . $punctuation;
    }


    /**
     * Render custom relationship view
     *
     * @param array $context Context
     * @param array $viewParts View parts
     *
     * @return string|null
     */
    public function renderCustomRelationshipView(Twig_Environment $twig, array $context, array $viewParts=[])
    {
        if (array_key_exists('property', $context) && ($context['property'] instanceof Relationship)) {
            if ($target = $context['property']->getTarget()) {
                if ($path = $this->templateFinder->buildPath($target, [], array_merge(['View', 'Collection', 'Inline'], $viewParts))) {
                    if ($twig->getLoader()->exists($path)) {
                        return $twig->render($path, $context);
                    }
                }
            }
        }

        return null;
    }


    /**
     * @see PropertyRecursiveAccess::get
     */
    public function getPropertyRecursive($object, $name, $addSourceObjectToResult)
    {
        return PropertyRecursiveAccess::get($object, $name, $addSourceObjectToResult);
    }


    /**
     * {@inheritdoc};
     */
    public function getName()
    {
        return 'appgear_extension';
    }
}