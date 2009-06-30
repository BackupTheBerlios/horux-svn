<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2007  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horux is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class HardwareAddList extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->getClientScript()->registerStyleSheetFile('treeCss','./protected/pages/hardware/assets/type.css');
    }

    public function displayHardwareList()
    {
        $path = './protected/pages/hardware/device/';

        $files = scandir($path);

        $types = array();
        foreach($files as $f)
        {
            if($f != '..' && $f != '.' && $f != '.svn' && is_dir($path.$f))
            {
                $t = explode("_", $f);
                $types[$t[0]][] = $t[1];
            }
        }
        $html = '<ul id="menu-item" class="jtree">';

        foreach($types as $k => $v)
        {
            $html .= '<li id="internal-node">';
            $html .= '<div class="node-open"><span></span>';
            $html .= Prado::localize("{type} technology",array('type'=>$k));
            $html .= '</div><ul>';

            foreach($v as $k2=>$v2)
            {

                $html .= '<li>';
                $html .= '<div class="leaf"><span></span>';
                $html .= '<a href="'.$this->Service->constructUrl('hardware.device.'.$k.'_'.$v2.'.add').'">'.$v2.'</a>';
                $html .= '</div>';
                $html .= '</li>';

            }

            $html .= '</ul>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
    
    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }
}
