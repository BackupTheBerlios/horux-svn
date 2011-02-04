<?php

class modattribution extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {
            $sql = "SELECT * FROM hr_vp_subscription_attribution WHERE id=:id";
            $cmd = $this->db->createCommand( $sql);
            $cmd->bindValue(":id",$this->Request["attid"], PDO::PARAM_INT);
            $query = $cmd->query();
            $data = $query->read();

            $sql2 = "SELECT * FROM hr_vp_subscription WHERE id=:id";
            $cmd2 = $this->db->createCommand( $sql2);
            $cmd2->bindValue(":id",$data['subcription_id'], PDO::PARAM_INT);
            $query2 = $cmd2->query();
            $data2 = $query2->read();

            $creditArray = array();
            if($data2['multiticket'] == 1)
            {
                for($i=0; $i<=$data2["credit"]; $i++)
                    $creditArray[] = array("Value"=>$i,"Text"=>$i);
            }
            else
                $creditArray[] = array("Value"=>0,"Text"=>0);
            
            $this->credit->DataSource = $creditArray;
            $this->credit->dataBind();

            if($data2['multiticket'] == 1)
                $this->credit->setSelectedValue($data2['credit']-$data['credit']);
            else
                $this->credit->setSelectedIndex(0);

            $this->createDate->Value = $data['create_date'];
            $this->createBy->Text = $data['create_by'];
            $this->type->Text = $data2['name'];

            if(substr($data['start'],0,10) != "0000-00-00") {
                $date = explode("-", substr($data['start'],0,10) );
                $this->start->Date = $date[2]."-".$date[1]."-".$date[0];
            }

            if(substr($data['end'],0,10) != "0000-00-00") {
                $date = explode("-", substr($data['end'],0,10) );
                $this->end->Date = $date[2]."-".$date[1]."-".$date[0];
            }

            if(substr($data['start'],11,8) != "00:00:00") {
                $time = explode(":", substr($data['start'],11,8) );
                $this->start_hours->Text = $time[0];
                $this->start_minutes->Text = $time[1];
                $this->start_secondes->Text = $time[2];
            }
            if(substr($data['end'],11,8) != "00:00:00") {
                $time = explode(":", substr($data['end'],11,8) );
                $this->end_hours->Text = $time[0];
                $this->end_minutes->Text = $time[1];
                $this->end_secondes->Text = $time[2];
            }

            $this->status->setSelectedValue($data['status']);

        }
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The attribution was modified successfully'), 'attid'=>$this->Request['attid'], 'userid'=>$this->Request['userid']);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modattribution', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The attribution was not modified'), 'attid'=>$this->Request['attid'], 'userid'=>$this->Request['userid']);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modattribution', $pBack));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The attribution was modified successfully'),'id'=>$this->Request['userid']);
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The attribution was not modified'),'id'=>$this->Request['userid']);

            $this->Response->redirect($this->Service->constructUrl('components.velopark.attribution',$pBack));
        }
    }

    protected function saveData() {
        $sql = "SELECT * FROM hr_vp_subscription_attribution WHERE id=:id";
        $cmd = $this->db->createCommand( $sql);
        $cmd->bindValue(":id",$this->Request["attid"], PDO::PARAM_INT);
        $query = $cmd->query();
        $data = $query->read();

        $cmd = $this->db->createCommand( "UPDATE hr_vp_subscription_attribution SET `credit` = :credit, `start` = :start,`end` = :end, `status` = :status WHERE id =:id" );

        $sql2 = "SELECT * FROM hr_vp_subscription WHERE id=:id";
        $cmd2 = $this->db->createCommand( $sql2);
        $cmd2->bindValue(":id",$data['subcription_id'], PDO::PARAM_INT);
        $query2 = $cmd2->query();
        $data2 = $query2->read();

        if($data2["multiticket"] == 1)
        {
            $credit =  $data2["credit"] - $this->credit->getSelectedValue();
            $cmd->bindValue(":credit",$credit,PDO::PARAM_STR);
        }
        else
            $cmd->bindValue(":credit",0,PDO::PARAM_STR);

        $start = "NULL";

        if($this->start->Date != "") {
            $start = explode("-",$this->start->Date);
            $start = $start[2]."-".$start[1]."-".$start[0];

            $start .= " ".$this->start_hours->SafeText.":";
            $start .= $this->start_minutes->SafeText.":";
            $start .= $this->start_secondes->SafeText;

        }

        $cmd->bindValue(":start",$start,PDO::PARAM_STR);

        $end = "NULL";
        if($this->start->Date != "") {
            $end = explode("-",$this->end->Date);
            $end = $end[2]."-".$end[1]."-".$end[0];

            $end .= " ".$this->end_hours->SafeText.":";
            $end .= $this->end_minutes->SafeText.":";
            $end .= $this->end_secondes->SafeText;

        }

        $cmd->bindValue(":end",$end, PDO::PARAM_STR);

        $cmd->bindValue(":status",$this->status->getSelectedValue(), PDO::PARAM_STR);

        $cmd->bindValue(":id",$this->Request['attid'], PDO::PARAM_STR);


        return $cmd->execute();
    }
}

?>