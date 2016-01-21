<?php
/**
 * Neevo extension for Nette Framework.
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file license.txt.
 *
 * Copyright (c) 2013 Smasty (http://smasty.net)
 *
 */

namespace Neevo\Nette;

use Nette\DI\CompilerExtension;

/**
 * Neevo extension for Nette Framework.
 * Creates services `manager`, `panel` and `cache`.
 */
class Extension extends CompilerExtension
{


    const VERSION = '1.2.2';


    public function loadConfiguration()
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig();

        $panelEvents = $container->parameters['productionMode']
            ? DebugPanel::EXCEPTION
            : DebugPanel::QUERY + DebugPanel::EXCEPTION;

        // Cache
        $cache = $container->addDefinition($this->prefix($c = 'cache'))
            ->setClass('Neevo\Nette\CacheAdapter', array(ucfirst($this->prefix($c))));

        // Manager
        $manager = $container->addDefinition($this->prefix('manager'))
            ->setClass('Neevo\Manager', array($config, $this->prefix('@cache')));

        // Panel
        $panelName = 'Neevo-Nette-DebugPanel-' . ucfirst($this->name);
        $panel = $container->addDefinition($this->prefix('panel'))
            ->setClass('Neevo\Nette\DebugPanel', array(ucfirst($this->name)))
            ->addSetup('$service->setExplain(?)', array(!$container->parameters['productionMode']))
            ->addSetup('Tracy\Debugger::getBar()->addPanel(?, ?)', array('@self', $panelName))
            ->addSetup(
                'Tracy\Debugger::getBlueScreen()->addPanel(?)',
                array(array('@self', 'renderException'))
            );

        $manager->addSetup('$service->attachObserver(?, ?)', array($panel, $panelEvents));
    }
}
