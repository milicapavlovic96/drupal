<?php

namespace Drupal\products_module\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;

class TwigExtension extends AbstractExtension
{
    //Ova ekstenzija se poziva u menu--main.html.twig
    public function getFunctions()
    {
        return [
            new TwigFunction('menuLink', [$this, 'menuLink']),
        ];
    }

    public function menuLink()
    {
        $config = \Drupal::config('products_module.settings');
        $fbLink = $config->get('fbLink');
        return $fbLink;
    }
}