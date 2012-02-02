<?php

/*
 * This file is part of Webasyst framework.
 *
 * Licensed under the terms of the GNU Lesser General Public License (LGPL).
 * http://www.webasyst.com/framework/license/
 *
 * @link http://www.webasyst.com/
 * @author Webasyst LLC
 * @copyright 2011 Webasyst LLC
 * @package wa-system
 */
class waLayout extends waController
{

    protected $blocks = array();
    protected $template = null;
    /**
     * @var waSmartyView
     */
    protected $view;
    /**
    * @var waTheme
    */
    protected $theme;
    

    public function __construct()
    {
        $this->view = waSystem::getInstance()->getView();
    }


    public function setBlock($name, $content)
    {
        if (isset($this->blocks[$name])) {
            $this->blocks[$name] .= $content;
        } else {
            $this->blocks[$name] = $content;
        }
    }

    public function executeAction($name, $action, waDecorator $decorator = null)
    {
        $action->setLayout($this);
        $content = $decorator ? $decorator->display($action) : $action->display();
        $this->setBlock($name, $content);
    }

    protected function getTemplate()
    {
        if ($this->template === null) {
            $prefix = waSystem::getInstance()->getConfig()->getPrefix();
            $template = substr(get_class($this), strlen($prefix), -6);
            return 'templates/layouts/' . $template . $this->view->getPostfix();
        } else {
            if (strpbrk($this->template, '/:') !== false) {
                return $this->template;
            }
            return 'templates/layouts/' . $this->template . $this->view->getPostfix();
        }
    }
    
    protected function setThemeTemplate($template)
    {
        $theme_path = $this->getTheme()->getPath();
        $this->view->assign('wa_theme_url', $this->getThemeUrl());
        $this->view->setTemplateDir($theme_path);
        $this->template = 'file:'.$template;
        return file_exists($theme_path.'/'.$template);
    }

    protected function getThemeUrl()
    {
        return $this->getTheme()->getUrl();
    }
    
    /**
     * Return current theme 
     * 
     * @return waTheme
     */
    public function getTheme()
    {
        if ($this->theme == null) {
            $theme = waRequest::getTheme();
            if (strpos($theme, ':') !== false) {
                list($app_id, $theme) = explode(':', $theme, 2);
            } else {
                $app_id = null;
            }
            $this->theme = new waTheme($theme, $app_id);
        }
        return $this->theme;
    }
        
    public function assign($name, $value)
    {
    	$this->blocks[$name] = $value;
    }

    public function execute()
    {

    }
    
    public function display()
    {
        $this->execute();
        $this->view->assign($this->blocks);
        waSystem::getInstance()->getResponse()->sendHeaders();
        $this->view->cache(false);
        if ($this->view->autoescape() && $this->view instanceof waSmarty3View) {
            $this->view->smarty->loadFilter('pre', 'content_nofilter');
        }
        $this->view->display($this->getTemplate());
    }
}

