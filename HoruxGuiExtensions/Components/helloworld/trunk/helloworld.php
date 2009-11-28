<?php

class helloworld extends Page
{
    // Surcharge de la fonction PRADO onLoad
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $cmd = $this->db->createCommand("SELECT text FROM hr_helloworld");
            $data = $cmd->query();

            // N'ayant qu'une seule ligne dans notre table, nous ne lisons que la première ligne
            $data = $data->read();

            //initialisation du champs helloworld
            $this->helloworld->Text = $data['text'];

        }
    }

    // cette fonction est appelée lorsque l'on clique sur le bouton Sauver
    public function onSave($sender, $param)
    {
      if($this->Page->IsValid)
      {
        if($this->saveData())
        {
          // message en cas de succès
          $pBack = array('okMsg'=>Prado::localize('The hello world text was saved correctly'));
        }
        else
        {
          // Message en cas d'erreur
          $pBack = array('koMsg'=>Prado::localize('The hello world text was not modified'));
        }

        // redirection de la page sur la même page. Nous pourrions aussi rediriger ailleurs si nécessaire
        $this->Response->redirect($this->Service->constructUrl('components.helloworld.helloworld',$pBack));
      }
    }


    public function saveData()
    {
      $cmd = $this->db->createCommand( "UPDATE hr_helloworld SET text=:helloworld" );

      $cmd->bindParameter(":helloworld",$this->helloworld->SafeText,PDO::PARAM_STR);

      // return false en cas d'échec ou de non modification
      if(!$cmd->execute()) return false;

      // ajout d'un commentaire dans les logs de Horux Gui
      $this->log("Modify the site configuration");

      return true;
    }
}

?>
