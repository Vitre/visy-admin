<?php

namespace Visy\Visy\Admin\ProjectsBundle\EventListener;

use Visy\Visy\Admin\Bundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{

    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $item = $menu->addChild('Projekty', [
            'route' => 'visy_admin_project'
        ]);
        $item->setCurrent(true);
    }
}