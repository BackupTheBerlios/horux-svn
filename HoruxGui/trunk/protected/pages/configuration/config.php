<?php


Prado::using('horux.pages.configuration.sql');

class Config extends Page {

    protected $emailError = '';

    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {
            $this->setData();
        }

        $superAdmin = $this->Application->getUser()->getSuperAdmin();
        $param = $this->Application->getParameters();

        if($param['appMode'] == 'demo' && $superAdmin == 0) {
            $this->tbb->Save->setEnabled(false);
        }
    }

    public function setData() {
        $cmd = $this->db->createCommand( SQL::SQL_GET_CONFIG );
        $query = $cmd->query();
        if($query) {
            $data = $query->read();

            $this->xmlrpc_port->Text = $data['xmlrpc_port'];
            $this->xmlrpc_server->Text = $data['xmlrpc_server'];
            $this->log_path->Text = $data['log_path'];
            $this->debug_mode->setChecked($data['debug_mode']);
            $this->key->Text = $data['key'];
            $this->publicurl->Text = $data['publicurl'];
            $this->picturepath->Text = $data['picturepath'];

            $this->mailer->SetSelectedValue($data['mail_mailer']);
            $this->mail_from->Text = $data['mail_mail_from'];
            $this->name_from->Text = $data['mail_from_name'];
            $this->sendmail_path->Text = $data['mail_sendmail_path'];



            if($data['mail_smtp_auth']) {
                $this->smtp_auth_yes->SetChecked(true);
                $this->smtp_auth_no->SetChecked(false);
            }
            else {
                $this->smtp_auth_no->SetChecked(true);
                $this->smtp_auth_yes->SetChecked(false);
            }

            $this->smtp_secure_none->SetChecked(false);
            $this->smtp_secure_tls->SetChecked(false);
            $this->smtp_secure_ssl->SetChecked(false);

            switch($data['mail_smtp_safe']) {
                case 'none':
                    $this->smtp_secure_none->SetChecked(true);
                    break;
                case 'tls':
                    $this->smtp_secure_tls->SetChecked(true);
                    break;
                case 'ssl':
                    $this->smtp_secure_ssl->SetChecked(true);
                    break;
            }

            $this->smtp_user->Text = $data['mail_smtp_username'];
            $this->smtp_password->Text = $data['mail_smtp_password'];
            $this->smtp_host->Text = $data['mail_smtp_host'];
            $this->smtp_port->Text = $data['mail_smtp_port'];
            $this->cards_format->Text = $data['cards_format'];
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {

            $res = $this->saveData();

            if($res && $this->emailError == '') {
                $pBack = array('okMsg'=>Prado::localize('The config was modified successfully'));
            }
            else {
                if($res && $this->emailError != '') {
                    $Text = Prado::localize('The config was modified successfully.');
                    $Text .= $this->emailError;
                    $pBack = array('koMsg'=>$Text);
                }
                else
                    $pBack = array('koMsg'=>Prado::localize('The config was not modified. ').$this->emailError);

            }
            $this->Response->redirect($this->Service->constructUrl('configuration.config',$pBack));
        }
    }

    public function saveData() {
        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_CONFIG );

        $cmd->bindValue(":xmlrpc_server",$this->xmlrpc_server->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":xmlrpc_port",$this->xmlrpc_port->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":log_path",$this->log_path->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":debug_mode",$this->debug_mode->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":key",$this->key->SafeText,PDO::PARAM_STR);

        $cmd->bindValue(":publicurl",$this->publicurl->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":picturepath",$this->picturepath->SafeText,PDO::PARAM_STR);


        $cmd->bindValue(":mail_mailer",$this->mailer->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":mail_mail_from",$this->mail_from->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":mail_from_name",$this->name_from->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":mail_sendmail_path",$this->sendmail_path->SafeText,PDO::PARAM_STR);

        $auth = $this->smtp_auth_yes->GetChecked() ? "1" : "0";
        $cmd->bindValue(":mail_smtp_auth",$auth,PDO::PARAM_STR);

        $secure = "none";
        if($this->smtp_secure_tls->GetChecked())
            $secure = "tls";
        if($this->smtp_secure_ssl->GetChecked())
            $secure = "ssl";
        $cmd->bindValue(":mail_smtp_safe",$secure,PDO::PARAM_STR);

        $cmd->bindValue(":mail_smtp_username",$this->smtp_user->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":mail_smtp_password",$this->smtp_password->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":mail_smtp_host",$this->smtp_host->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":mail_smtp_port",$this->smtp_port->SafeText,PDO::PARAM_STR);

        $cmd->bindValue(":cards_format",$this->cards_format->SafeText,PDO::PARAM_STR);

        $_SESSION['helpKey'] = $this->key->SafeText;

        $cmd->execute();

        $res = false;
        if($this->mail_from->SafeText != "")
            $res = $this->sendEmailConf();

        if(!$res)
            $this->emailError = Prado::localize('The confirmation email cannot be sended. Please check your mail settings');
        else
            $this->emailError = '';

        $this->log("Modify the global configuration");

        return true;
    }

    protected function sendEmailConf() {
        $mailer = new TMailer();
        return $mailer->sendConfigChange();
    }
}

?>
