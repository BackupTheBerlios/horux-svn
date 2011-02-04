<?php


Prado::using('horux.pages.key.sql');

class recycling extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->setHoruxSysTray(true);

        if(!$this->isPostBack)
        {

            if(isset($this->Request['sn']))
            {
                $this->label->Text = Prado::localize("The key was recycled");

                $cmd = $this->db->createCommand( "UPDATE hr_keys SET isUsed=0 WHERE serialNumber=:serialNumber" );
                $cmd->bindValue(":serialNumber",$this->Request['sn'], PDO::PARAM_STR);
                $res1 = $cmd->execute();

                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:serialNumber" );
                $cmd->bindValue(":serialNumber",$this->Request['sn'], PDO::PARAM_STR);
                $query = $cmd->query();

                if($query)
                {
                    $data = $query->read();

                    $cmd = $this->db->createCommand( "DELETE FROM hr_keys_attribution WHERE id_key=:id" );
                    $cmd->bindValue(":id",$data['id'], PDO::PARAM_STR);
                    $res1 = $cmd->execute();

                }


            }
        }
    }

    public function onClearLavel($sender, $param)
    {
        $this->clearLabel->stopTimer();

        $this->label->Text = Prado::localize("Present the key to the reader for the recycle");
    }
}


?>
