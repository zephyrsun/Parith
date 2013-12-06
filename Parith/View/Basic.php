<?php

/**
 * Basic View
 *
 * Parith :: a compact PHP framework
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\View;

use \Parith\Log;

class Basic extends \Parith\Result
{
    public $options = array(
        'source_dir' => null,
        'source_ext' => 'php',
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + \Parith\App::getOption('view') + $this->options;

        $this->options['source_dir'] or $this->options['source_dir'] = BASE_DIR . 'View';
    }

    /**
     * @param string $name
     * @param string $ext
     * @return void
     */
    public function render($name, $ext = null)
    {
        $name = $this->getSourceFile($name, $ext);

        \extract($this->resultGet(), EXTR_SKIP);

        include $name;
    }

    /**
     * @param string $name
     * @param string $ext
     * @return mixed
     */
    public function fetch($name, $ext = null)
    {
        \ob_start();
        $this->render($name, $ext);
        return \ob_get_clean();
    }

    /**
     * @param $name
     * @param $ext
     * @return string
     * @throws \Parith\Exception
     */
    public function getSourceFile($name, $ext)
    {
        $ext or $ext = $this->options['source_ext'];

        $name = $this->options['source_dir'] . DIRECTORY_SEPARATOR . $name . '.' . $ext;

        if (\is_file($name))
            return $name;

        Log::write('View file "' . $name . '" not found');
    }
}