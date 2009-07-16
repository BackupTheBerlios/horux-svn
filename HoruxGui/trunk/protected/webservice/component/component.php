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

class Component
{
    /**
     * @param mixed $params the function name and parameters
     * @return mixed the component service response
     * @soapmethod
     */
    public function callServiceComponent($params)
    {
        if(is_array($params))
        {            
            if(array_key_exists('component', $params))
            {
                if(array_key_exists('component', $params))
                {
                    if(array_key_exists('function', $params))
                    {
                        Prado::using('horux.pages.components.'.$params['component'].'.webservice.'.$params['class']);

                        $comp = new $params['class'];
                        if(method_exists($comp, $params['function']))
                        {
                            if(array_key_exists('params', $params))
                                return $comp->$params['function']($params['params']);
                            else
                                return $comp->$params['function']();

                        }
                        else
                            return -4; //Function not exists

                    }
                    else
                    {
                        return -3; //!Missing function
                    }
                }
                else
                    return -2; //! Missing class
            }
            else
            {
                return -1; //!Missing component
            }
        }
        else
            return false;

    }


}

?>
