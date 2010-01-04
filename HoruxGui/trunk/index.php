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

// Define if the web site musst be in the SSL mode
define("SSL", false);

// Define if horux gui is working in Saas mode
define("SAAS", true);


// In SSL mode, be sure to be in SSL
if(SSL)
{
    // redirect to 443 if not
    if($_SERVER["SERVER_PORT"] == 80)
    {
        header("location:https://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]);
        exit;
    }
}

$frameworkPath='./prado/framework/prado.php';

// The following directory checks may be removed if performance is required
$basePath=dirname(__FILE__);
$assetsPath=$basePath.'/assets';
$runtimePath=$basePath.'/protected/runtime';

if(!is_file($frameworkPath))
	die("Unable to find prado framework path $frameworkPath.");
if(!is_writable($assetsPath))
	die("Please make sure that the directory $assetsPath is writable by Web server process.");
if(!is_writable($runtimePath))
	die("Please make sure that the directory $runtimePath is writable by Web server process.");


require_once($frameworkPath);

if(!file_exists('./protected/runtime/.installed'))
{
	$app_conf = new TApplicationConfiguration();
	$app_conf->loadFromFile('./protected/pages/install/application.xml');

	$application=new TApplication('protected', true);
	$application->applyConfiguration($app_conf, true);
	$application->run();
}
else
{
    $session=new THttpSession;
    $session->open();

    $application=new TApplication('protected', true);
    $app_conf = new TApplicationConfiguration();
    $config_file = './protected/application_p.xml';

    if(SAAS)
    {
        $username = "";
        if(isset($_REQUEST['username']))
        {
            $username = $_REQUEST['username'];
        }
        else
        {
            if(isset($_REQUEST['ctl0$Main$username']))
            {
                $username = $_REQUEST['ctl0$Main$username'];
            }
        }

        if($username !== "")
        {
            if(($pos = strpos($username, '@')) !== false)
            {
                $domain = strstr($username, '@');
                $user = substr($username,0, $pos);
                $domain = substr($domain,1, strlen($domain)-1);


                if(file_exists('./protected/application_'.$domain.'.xml'))
                {
                    $config_file = './protected/application_'.$domain.'.xml';
                    $session['application.xml']=$config_file;

                    $_REQUEST['ctl0$Main$username'] = $user;
                    $_POST['ctl0$Main$username'] = $user;
                    $_REQUEST['username'] = $user;
                    $_GET['username'] = $user;
                }
                else
                {
                    $_REQUEST['ctl0$Main$username'] = "";
                    $_POST['ctl0$Main$username'] = "";
                    $_REQUEST['username'] = "";
                    $_GET['username'] = "";
                }
            }
            else
            {
                $_REQUEST['ctl0$Main$username'] = "";
                $_POST['ctl0$Main$username'] = "";
                $_REQUEST['username'] = "";
                $_GET['username'] = "";
            }
        }
    }

    if($session['application.xml'])
    {
        $config_file = $session['application.xml'];
    }

    $session->close();

    $app_conf->loadFromFile($config_file);
    $application->applyConfiguration($app_conf, true);
    $application->run();

}	


	
?>
