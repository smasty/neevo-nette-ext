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

use Exception;
use Neevo\Manager;
use Neevo\NeevoException;
use Neevo\ObservableInterface;
use Neevo\ObserverInterface;
use Neevo\Result;
use Tracy\BlueScreen;
use Tracy\Helpers;
use Tracy\IBarPanel;

/**
 * Debug panel informing about performed queries.
 */
class DebugPanel implements ObserverInterface, IBarPanel
{


    /** @var string */
    private $name;

    /** @var array */
    private $tickets = array();

    /** @var int */
    private $numQueries = 0;

    /** @var int */
    private $totalTime = 0;

    /** @var array */
    private $failedQuerySource;

    /** @var bool */
    private $explain = true;


    /**
     * @param string $name
     */
    public function __construct($name = 'Default')
    {
        $this->name = $name;
    }


    /**
     * @return bool
     */
    public function getExplain()
    {
        return $this->explain;
    }


    /**
     * @param bool $explain
     */
    public function setExplain($explain)
    {
        $this->explain = (bool) $explain;
    }


    /**
     * @return array
     */
    public function getTickets()
    {
        return $this->tickets;
    }


    /**
     * @return int
     */
    public function getNumQueries()
    {
        return $this->numQueries;
    }


    /**
     * @return int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }


    /**
     * Receives update from observable subject.
     * @param ObservableInterface $subject
     * @param int $event
     */
    public function updateStatus(ObservableInterface $subject, $event)
    {
        $source = null;
        if (class_exists('Neevo\Manager')) {
            $class = new \ReflectionClass('Neevo\Manager');
            $path = dirname($class->getFileName());
            foreach (debug_backtrace(false) as $t) {
                if (isset($t['file']) && strpos($t['file'], $path) !== 0) {
                    $source = array($t['file'], (int) $t['line']);
                    break;
                }
            }
        }

        if ($event & self::QUERY) {
            $this->numQueries++;
            $this->totalTime += $subject->getTime();

            if ($subject instanceof Result) {
                try {
                    $rows = count($subject);
                } catch (Exception $e) {
                    $rows = '?';
                }
                $explain = $this->explain ? $subject->explain() : null;
            } else {
                $rows = '-';
            }

            $this->tickets[] = array(
                'sql' => (string) $subject,
                'time' => $subject->getTime(),
                'rows' => $rows,
                'source' => $source,
                'connection' => $subject->getConnection(),
                'explain' => isset($explain) ? $explain : null
            );
        } elseif ($event === self::EXCEPTION) {
            $this->failedQuerySource = $source;
        }
    }


    /**
     * Renders SQL query string to Nette debug bluescreen when available.
     * @param NeevoException $e
     * @return array
     */
    public function renderException($e)
    {
        if (!($e instanceof NeevoException && $e->getSql())) {
            return;
        }
        list($file, $line) = $this->failedQuerySource;
        return array(
            'tab' => 'SQL',
            'panel' => Manager::highlightSql($e->getSql())
                . '<p><b>File:</b> ' . Helpers::editorLink($file, $line) . " &nbsp; <b>Line:</b> $line</p>"
                . ($line ? BlueScreen::highlightFile($file, $line) : '')
                . 'Neevo v' . Manager::VERSION
        );
    }


    /**
     * Renders Nette DebugBar tab.
     * @return string
     */
    public function getTab()
    {
        $name = $this->name;
        $queries = $this->numQueries;
        $time = $this->totalTime;
        ob_start();
        include __DIR__ . '/templates/DebugPanel.tab.phtml';
        return ob_get_clean();
    }


    /**
     * Renders Nette DebugBar panel.
     * @return string
     */
    public function getPanel()
    {
        if (!$this->numQueries) {
            return '';
        }

        $name = $this->name;
        $tickets = $this->tickets;
        $totalTime = $this->totalTime;
        $numQueries = $this->numQueries;

        ob_start();
        include __DIR__ . '/templates/DebugPanel.panel.phtml';
        return ob_get_clean();
    }
}
