<?php
require_once('vtpl/vtpl.php');

#[\AllowDynamicProperties]
class View
{
    /**
     * private variable that holds the instance handle
     */
    static private $instance = NULL;

    /**
     * This is the only way to get a instantce of the class
     */
    static function get_instance()
    {
        if (self::$instance === NULL) {
            self::$instance = new view(); //create class instance
        }
        return self::$instance;
    }

    function set($array)
    {
        if ($array)
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
    }
    /**
     * Declare the constructor private to avoid object initialization, this is a singleton
     */
    private function __construct() {}

    private function recompile($filename, $file)
    {
        $vtpl = new Vtpl();
        $vtpl->addTemplatePath(dirname(__DIR__) . '/../tpl/');
        $vtpl->htmlPath =   dirname(__DIR__) . '/../templates/frontend/';

        $vtpl->loadHtmlTemplate($filename);
        $vtpl->loadTemplateFileFromPath(str_replace('.html', '.tpl', $filename));
        $vtpl->saveCompiledTemplate($file);
    }

    /**
     * Renders the specified file (html)
     *
     * @param string @filename
     */
    function render($filename = null)
    {
        $file           =   dirname(__DIR__) . '/../compiled_templates/' . $filename;
        $htmlFile       =   dirname(__DIR__) . '/../templates/frontend/' . $filename;
        $templateFile =   dirname(__DIR__) . '/../tpl/' . str_replace('.html', '.tpl', $filename);

        if (
            !file_exists($file)
            || (max(
                @filemtime($htmlFile),
                @filemtime($templateFile)
            )
                > @filemtime($file))
            || (defined('VTPL_DEBUG') && VTPL_DEBUG)
        ) {
            self::recompile($filename, $file);
        }
        include_once($file);
    }
}
