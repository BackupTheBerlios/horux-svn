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

class component
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
            if(array_key_exists(0, $params)) //component
            {
                if(array_key_exists(1, $params)) // class
                {
                    if(array_key_exists(2, $params)) //function
                    {
                        try
                        {
                            Prado::using('horux.pages.components.'.$params[0].'.webservice.'.$params[1]);
                            $comp = new $params[1];
                            if(method_exists($comp, $params[2]))
                            {
                                if(array_key_exists(3, $params))
                                    return $comp->$params[2]($params[3]);
                                else
                                    return $comp->$params[2]();

                            }
                            else
                                return -4; //Function not exists
                        }
                        catch(Exception $e)
                        {

                            return -5; //class not exists
                        }
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
