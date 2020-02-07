<?php

namespace Drupal\products_module\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;

class TwigExtension extends AbstractExtension
{

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
        //Proveravamo da li vec postoji ovaj link u meniju
        $menu_link2 = $query = \Drupal::entityQuery('menu_link_content')
        ->condition('menu_name', 'main')
        ->condition('link.uri', 'https://www.facebook.com')
        ->execute();
        if($menu_link2==null){
            $menu_link = MenuLinkContent::create([
                'title' => 'Link',
                'link' => ['uri' => $fbLink],
                'menu_name' => 'main',
                'expanded' => TRUE,
                'weight' => 0,
            ]);
            $menu_link->save();
            return 'sadasd';
        }
    }
}