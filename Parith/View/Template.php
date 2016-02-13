<?php

/**
 * Template
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 20092016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\View;

use \Parith\Lib\File as LibFile;
use \Parith\Cache\File as CacheFile;

class Template extends Basic
{
    public $cache, $options = array(
        'source_dir' => null,
        'source_ext' => 'html',
        'cache_dir' => null,
        'ldelim' => '{{',
        'rdelim' => '}}',
    );


    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (!$dir = $this->options['cache_dir'])
            $dir = \APP_DIR . 'tmp' . \DIRECTORY_SEPARATOR . 'template';

        $this->cache = new CacheFile(array('dir' => $dir));
    }

    /**
     * @param string $name
     * @param string $ext
     */
    public function render($name, $ext = '')
    {
        $source = $this->getSourceFile($name, $ext);
        $target = $this->cache->filename(\rawurlencode($name));
        if (LibFile::isNewer($source, $target))
            LibFile::touch($target, self::parse(\file_get_contents($source), $this->options['ldelim'], $this->options['rdelim']), false);

        include $target;
    }

    /**
     * @param $tag
     * @param $ldelim
     * @param $rdelim
     * @return mixed
     */
    static public function parse($tag, $ldelim, $rdelim)
    {
        return \preg_replace_callback(
            '/' . $ldelim . '([^' . $ldelim . $rdelim . ']+)' . $rdelim . '/', //  '/{([^{}]+)}/'
            '\Parith\View\Template::parseBrace', $tag //\stripslashes($tag)
        );
    }

    /**
     * @param $str
     * @return string
     */
    static public function parseBrace($str)
    {
        $p = $r = array();

        // {if $foo}
        $p[] = '/^if\s+(.+)$/';
        $r[] = '<?php if(\\1) { ?>';

        // {else}
        $p[] = '/^else$/';
        $r[] = '<?php } else { ?>';

        // {elseif}
        $p[] = '/^elseif\s+(.+?)$/';
        $r[] = '<?php } elseif (\\1) { ?>';

        // {foreach $name as $key => $val}
        $p[] = '/^foreach\s+(\S+)\s+as\s+(\S+(\s*=>\s*\S+)?)$/';
        $r[] = '<?php if(\is_array(\\1)) foreach(\\1 as \\2) { ?>';

        // {while $a}
        $p[] = '/^while\s+(\S+)$/';
        $r[] = '<?php while (\\1) { ?>';

        // {break}
        $p[] = '/^break$/';
        $r[] = '<?php break; ?>';

        // {continue}
        $p[] = '/^continue(\s+\d+)?$/';
        $r[] = '<?php continue\\1; ?>';

        // ending
        $p[] = '/^(\/if|\/foreach|\/while)$/';
        $r[] = '<?php } ?>';

        //variable {$foo}, {\App::$foo}
        //const {PARITH_DIR}, {\App::PARITH_DIR}
        //method {date('Y-m-d', \APP_TS)}, {\Router::path()}
        $p[] = '/^(.+::)?(\$\w+[^\s}]*|[A-Z_]*|[^\(\s]+\(.*\))$/';
        $r[] = '<?php echo \\0; ?>';

        $s = \preg_replace($p, $r, $str[1]);

        // parse vars
        $s = \preg_replace_callback('/(?<!::)\$[^_][^\d\s}\(\)]+/', '\Parith\View\Template::parseVar', $s);

        // parse include
        $s = \preg_replace_callback('/^include\s+([^}]+)$/', '\Parith\View\Template::parseInclude', $s);

        return $s;
    }

    /**
     * @param $val
     * @return string
     */
    static protected function parseVar($val)
    {
        return \preg_replace(
            array(
                '/\.(\w+)/', // replace $foo.bar.str to $foo['bar']['str']
                '/\$(?!this->)(\w+)/'),
            array(
                "['\\1']",
                '$this->\\1'
            ),
            $val[0]);
    }

    /**
     * @param $val
     * @return string
     */
    static protected function parseInclude($val)
    {
        return '<?php $this->load(' . self::propExport($val[1]) . '); ?>';
    }

    /**
     * @param $str
     * @return string
     */
    static public function propExport($str)
    {
        // \$[^=\s]+ : variables
        // \'[^\']*\' : single quoted string
        // "[^"]*" : double quoted string
        // [^"\'=\s]+ : other string
        //\preg_match_all('/([^=\s]+)=(\'[^\']+\'|"[^"]+"|\S+)/', $str, $match, PREG_SET_ORDER);

        return 'array(' . \preg_replace('/([^=\s]+)(=)(\'[^\']+\'|"[^"]+"|\S+)/', "'\\1'\\2>\\3,", $str) . ')';
    }

    /**
     * @param $data
     * @return $this
     */
    public function load($data)
    {
        $this->resultSet($data);

        //$this->render($this->_data['file']);
        $this->render($this->file, $this->ext);

        // avoid collision
        parent::resultDelete($data);

        return $this;
    }
}