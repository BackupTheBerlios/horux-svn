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

include('class.update.php');

class Update extends PageList
{

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $update = new HoruxGuiUpdate();

            $res = $update->compareFiles();

            if(is_array($res)) {

                if(count($res) > 0 ) {
                    $this->displayMessage(Prado::localize("Your version is not up to date"), false);

                    $this->DataGrid->DataSource=$res;
                    $this->DataGrid->dataBind();
                } else {
                    $this->displayMessage(Prado::localize("Your version is up to date"), true);

                }

            } else {
                $this->displayMessage($res, false);
            }
        }
    }

    public function onUpdate($sender, $param) {

        $items = $this->DataGrid->getDataSource();

        foreach ($this->DataGrid->items as $item){
            
            $fileToUpdate = $item->name->Text;

            if($item->md5->Text != "") {

                $update = new HoruxGuiUpdate();

                $newFile = $update->updateFile($fileToUpdate);

                if($newFile) {
                    if(($handle = fopen($fileToUpdate, "w"))) {
                        fwrite($handle,$newFile);

                        fclose($handle);
                    }
                }

            } else {
                if(!file_exists($fileToUpdate))
                    mkdir($fileToUpdate);
            }
        }

        $this->Response->redirect($this->Service->constructUrl('update.Update'));
    }
}

?>
