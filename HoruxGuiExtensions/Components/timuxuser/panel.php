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

class panel extends Page
{
    public function isAccess($page)
    {
		$app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
      	$db->Active=true;

		$usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();
		$groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID();

		$sql = 	'SELECT `allowed`, `shortcut` FROM hr_gui_permissions WHERE ' .
				'(`page`=\''.$page.'\' OR `page` IS NULL) ' .
				"AND (" .
					"(`selector`='user_id' AND `value`=".$usedId.") " .
					"OR (`selector`='group_id' AND `value`=".$groupId.") " .
				")" .
			'ORDER BY `page` DESC';

		$cmd = $db->createCommand($sql);
		$res = $cmd->query();
		$res = $res->readAll();
		// If there were no results
		if (!$res)
			return false;
		else
			// Traverse results
			foreach ($res as $allowed)
			{
				// If we get deny here
				if (! $allowed)
					return false;
			}

		return true;
    }

}

?>
