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
	$app_conf = new TApplicationConfiguration();
	$app_conf->loadFromFile('./protected/application_p.xml');

	$application=new TApplication('protected', true);
	$application->applyConfiguration($app_conf, true);
	$application->run();
}	


	
?>
