<?php


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
		    if(file_exists($fileToUpdate))
		      unlink($fileToUpdate);

                    if(($handle = fopen($fileToUpdate, "w"))) {
                        fwrite($handle,$newFile);

                        fclose($handle);

                        chmod($fileToUpdate, 0777);
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
