<?php


class gantner_AccessTerminal_standalone extends TDeviceStandalone
{
    private $db = NULL;

    public function init($config)
    {
        parent::init($config);

    }

    public function addStandalone($function, $id, $param=NULL)
    {
        // do not handle a block or unblock function
        /*if($function == 'block' || $function == 'unblock')
        {
            return;
        }*/

        $this->db = $this->Application->getModule('horuxDb')->DbConnection;
        $this->db->Active=true;

        switch($param)
        {
            case 'KeyList': // del,block,unblock key
                if($function == 'block' || $function == 'unblock')
                {
                   $function =  $function == 'block' ? 'sub' : 'add';
                }

                $this->addSubKey($function, $id);
                break;
            case 'KeyAdd':  // add a key with a user relation
                $this->addSubKey($function, $id);
                break;
            case 'KeyMod':  // mod a key
                $this->addSubKey($function, $id);
                break;
            case 'UserListAdd': // add a new user
                $this->addSubUser($function, $id);
                break;
            case 'UserListMod': // modify the user data
                $this->addSubUser($function, $id);
                break;
            case 'UserList': //block/unblock the user
                if($function == 'block' || $function == 'unblock')
                {
                   $function =  $function == 'block' ? 'sub' : 'add';
                   $this->addSubAllKeys($function, $id);
                }
                else
                    $this->addSubUser($function, $id);
                break;
            case 'UserWizzard':
                $this->addSubUser($function, $id);
                break;
            case 'UserAttributionKey':
                if($function == 'block' || $function == 'unblock')
                {
                   $function =  $function == 'block' ? 'sub' : 'add';
                }
                $this->addSubKey($function, $id);
                break;
            case 'UserAttributionGroup':
                $this->addSubGroup($function, $id);
                break;
            case 'UserGroupMod':
                if($function == 'add')
                    $this->addSubGroup($function, $id);
                break;
            case 'reinit':
                $this->reinit($id);
                break;
        }
    }

    protected function reinit($deviceId)
    {
        $cmd=$this->db->createCommand("SELECT COUNT(*) AS n FROM hr_gantner_standalone_action WHERE type='reinit' AND deviceId=".$deviceId);
        $data = $cmd->query();
        $data = $data->read();

        if($data['n']>0)
            return;

        $type = "reinit";
        $func = "add";
        $keyId = 0;
        $userId = 0;

        $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`) VALUES (:type,:func,:userId,:keyId,:deviceId)");
        $cmd->bindParameter(":type", $type);
        $cmd->bindParameter(":func", $func);
        $cmd->bindParameter(":userId", $userId );
        $cmd->bindParameter(":keyId", $keyId );
        $cmd->bindParameter(":deviceId", $deviceId );
        $cmd->execute();

    }


    protected function addSubAllKeys($function, $userId)
    {
        $cmd=$this->db->createCommand("SELECT t.id, t.serialNumber, t.isBlocked FROM hr_keys_attribution AS ta LEFT JOIN hr_keys AS t ON t.id=ta.id_key WHERE id_user=:id");
        $cmd->bindParameter(":id",$userId);
        $data = $cmd->query();
        $data = $data->readAll();

        //pour chaque clé
        foreach($data as $d)
        {
            $this->addSubKey($function, $d['id']);

            
            /*$cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param)");
            $cmd->bindParameter(":func",$function);
            $cmd->bindParameter(":type",$type);
            $cmd->bindParameter(":userId",$idperson);
            $cmd->bindParameter(":keyId",$idtag);
            $cmd->bindParameter(":deviceId",$idreader);
            $cmd->bindParameter(":param",$rfid);
            $cmd->execute();*/
        }
    }

    protected function addSubKey($function, $idkey)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_keys WHERE id=:id");
        $cmd->bindParameter(":id",$idkey);
        $data = $cmd->query();
        $data = $data->read();
        
        $type = "key";

        $rfid = $data['serialNumber'];
        $idtag = $data['id'];

        $cmd=$this->db->createCommand("SELECT * FROM hr_keys_attribution WHERE id_key=:id");
        $cmd->bindParameter(":id",$idtag);
        $data2 = $cmd->query();
        $data2 = $data2->readAll();

        //pour chaque groupe
        foreach($data2 as $d2)
        {
            $idperson = $d2['id_user'];

            $cmd=$this->db->createCommand("SELECT * FROM hr_user WHERE id=:id");
            $cmd->bindParameter(":id",$idperson);
            $data_u = $cmd->query();
            $data_u = $data_u->read();


            $cmd=$this->db->createCommand("SELECT gac.id_device FROM hr_user_group_attribution AS ga
                                           LEFT JOIN hr_user_group_access AS gac ON gac.id_group=ga.id_group
                                           LEFT JOIN hr_device AS d ON d.id = gac.id_device
                                           WHERE ga.id_user=:id AND d.type='gantner_AccessTerminal'");

            $cmd->bindParameter(":id",$idperson);
            $data3 = $cmd->query();
            $data3 = $data3->readAll();

            foreach($data3 as $d3)
            {
                $idreader = $d3['id_device'];

                if($idreader == '') continue;

                $cmd=$this->db->createCommand("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param)");
                $cmd->bindParameter(":func",$function);
                $cmd->bindParameter(":type",$type);
                $cmd->bindParameter(":userId",$idperson);
                $cmd->bindParameter(":keyId",$idtag);
                $cmd->bindParameter(":deviceId",$idreader);
                $cmd->bindParameter(":param",$rfid);
                $cmd->execute();
            }

        }
    }

    protected function addSubUser($function, $userId)
    {

        $cmd=$this->db->createCommand("SELECT * FROM hr_keys_attribution WHERE id_user=:id");
        $cmd->bindParameter(":id",$userId);
        $data3 = $cmd->query();
        $data3 = $data3->readAll();

        foreach($data3 as $d)
            $this->addSubKey($function,$d['id_key']);
    }


    protected function addSubGroup($function, $ids)
    {
        $ids = explode(",", $ids);
        $idgroup = $ids[0];
        $idperson = $ids[1];

        $cmd=$this->db->createCommand("SELECT * FROM hr_keys_attribution WHERE id_user=:id");
        $cmd->bindParameter(":id",$idperson);
        $data3 = $cmd->query();
        $data3 = $data3->readAll();

        foreach($data3 as $d)
            $this->addSubKey($function,$d['id_key']);
    }
}


?>
