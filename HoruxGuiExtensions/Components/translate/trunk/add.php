<?php


class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

		if(!is_writeable(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages"))
			$this->displayMessage(Prado::localize("The directory {dir} must be writeable", array("dir"=>".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages")), false);
			

    }
    
    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The language was added successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The language was not added'));

          $this->Response->redirect($this->Service->constructUrl('components.translate.translate',$pBack));
        }
    }


	public function checkIso($sender, $param)
	{
		if(file_exists(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$this->iso639->SafeText))
			$param->IsValid=false;
	}

	protected function saveData()
	{

		if(!is_writeable(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages"))
			return false;

		if(!mkdir(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$this->iso639->SafeText))	
			return false;


		$iso = $this->iso639->SafeText;
		$langName = $this->name->SafeText;
		$date = date('j/m/Y');
		$author = $this->author->SafeText;
		$email = $this->email->SafeText;
		$website = $this->website->SafeText;
		$copyright = $this->copyright->SafeText;
		$licence = $this->licence->SafeText;
		$description = $this->description->SafeText;

		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<install type=\"language\">\n";
		$xml .= "\t<installName>$iso</installName>\n";
		$xml .= "\t<name>$langName</name>\n";
		$xml .= "\t<version>1.0.0</version>\n";
		$xml .= "\t<creationDate>$date</creationDate>\n";
		$xml .= "\t<author>$author</author>\n";
		$xml .= "\t<authorEmail>$email</authorEmail>\n";
		$xml .= "\t<authorUrl>$website</authorUrl>\n";
		$xml .= "\t<copyright>$copyright</copyright>\n";
		$xml .= "\t<license>$licence</license>\n";
		$xml .= "\t<description>$description</description>\n";
		$xml .= "</install>\n";

		file_put_contents(".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$this->iso639->SafeText.DIRECTORY_SEPARATOR."install.xml", $xml);

		return true;
	}
}