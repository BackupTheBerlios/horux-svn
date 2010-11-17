<?php

class velopark extends TService
{
/*    protected $db = NULL;

    function __construct()
    {
      $this->db = $this->Application->getModule('horuxDb')->DbConnection;
      $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

      $this->db->Active=true;
    }*/

    public function syncStandalone($ids)
    {
	$app = Prado::getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $db->Active=true;

        $ids = explode(",", $ids);
        $idsOk = array();
        foreach($ids as $id)
        {

            $sql = "SELECT COUNT(*) AS n FROM hr_gantner_standalone_action WHERE id=".$id;
            $cmd= $db->createCommand($sql);
            $data = $cmd->query();
            $data = $data->read();
            if($data['n'] > 0)
            {

                $sql = "DELETE FROM hr_gantner_standalone_action WHERE id=".$id;


                $cmd= $db->createCommand($sql);

                if($cmd->execute())
                {
                    $idsOk[] = $id;
                }
            }
            else
                $idsOk[] = $id;

        }

        return implode(",", $idsOk);
    }

   /* public function buySub($params)
    {
        if(is_array($params))
        {
            if(array_key_exists('userId', $params) && array_key_exists('subId', $params))
            {
                $sql = "SELECT COUNT(*) AS n FROM hr_vp_subscription_attribution WHERE user_id=:id AND status='started'";
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['userId']);
                $data = $cmd->query();
                $data = $data->read();

                $nStarted = $data['n'];

                $sql = "SELECT * FROM hr_vp_subscription WHERE id=:id";
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['subId']);
                $data = $cmd->query();
                $data = $data->read();



                if($data["start"] == 'firstaccess' || $nStarted>0)
                    $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by) VALUES (:user_id,  :subcription_id, NOW(), 'not_start', :credit, 'NULL', 'NULL', :create_by)";
                else
                {
                    $validity = explode(':', $data["validity"]);
                    $nHours = ($validity[0]*365*24) + ($validity[1]*30*24) + ($validity[2]*24) + ($validity[3]);


                    $sql = "INSERT INTO hr_vp_subscription_attribution (user_id, subcription_id, create_date, status, credit, start, end, create_by) VALUES (:user_id,  :subcription_id, NOW(), 'started', :credit, NOW(), NOW()+ INTERVAL ".$nHours." HOUR, :create_by)";
                }

                $cmd=$this->db->createCommand($sql);

                $cmd->bindValue(":user_id",$params['userId'],PDO::PARAM_STR);
                $cmd->bindValue(":subcription_id",$params['subId'],PDO::PARAM_STR);
                if($data["start"] == 'firstaccess'  || $nStarted>0)
                    $cmd->bindValue(":credit",$data["credit"],PDO::PARAM_STR);
                else
                {
                    $credit = $data["credit"]-1;
                    $cmd->bindValue(":credit",$credit,PDO::PARAM_STR);
                }


                $createBy ="Vélorux";
                $cmd->bindValue(":create_by",$createBy,PDO::PARAM_STR);

                $cmd->execute();

                return true;
            }
            else
                return -1;
        }
        else
            return -1;

    }

    public function getSub($params)
    {
        if(is_array($params))
        {
            if(array_key_exists('id', $params))
            {
                 $sql = "SELECT id, name, price, description FROM hr_vp_subscription WHERE id=:id";

                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['id'],PDO::PARAM_STR);
                $data = $cmd->query();
                if($data)
                {
                    return $data->read();

                }
                else
                    return false;
            }
            else
                return -1;
        }
        else
            return -1;
    }

    public function getSubList()
    {
        $sql = "SELECT id, name, price FROM hr_vp_subscription";

        $cmd=$this->db->createCommand($sql);
        $cmd->bindValue(":id",$params['userId'],PDO::PARAM_STR);
        $data = $cmd->query();
        if($data)
        {
            return $data->readAll();

        }
        else
            return false;

    }


    public function getLastAccess($params)
    {
        if(is_array($params))
        {
            if(array_key_exists('userId', $params))
            {
                $limit = array_key_exists('nbre', $params) ? " LIMIT 0, ".$params['nbre'] : "";

                $sql = "SELECT * FROM hr_tracking WHERE id_user=:id ORDER by id DESC $limit";

                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['userId'],PDO::PARAM_STR);
                $data = $cmd->query();
                if($data)
                {
                    return $data->readAll();

                }
                else
                    return false;

            }
            else
                return -1;
        }
        else
            return -1;
        
    }

    public function setProfile($params)
    {
        if(is_array($params))
        {
            if(array_key_exists('userId', $params))
            {
                $sql = "";
                if($params['password'] !== "" )
                    $sql = "UPDATE hr_user SET name=:name, firstname=:firstname, street=:street, zip=:zip, city=:city, phone1=:phone1, email1=:email1, password=:password WHERE id=:id";
                else
                    $sql = "UPDATE hr_user SET name=:name, firstname=:firstname, street=:street, zip=:zip, city=:city, phone1=:phone1, email1=:email1 WHERE id=:id";
                    
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['userId'],PDO::PARAM_STR);
                $cmd->bindValue(":name",$params['name'],PDO::PARAM_STR);
                $cmd->bindValue(":firstname",$params['firstname'],PDO::PARAM_STR);
                $cmd->bindValue(":street",$params['street'],PDO::PARAM_STR);
                $cmd->bindValue(":zip",$params['zip'],PDO::PARAM_STR);
                $cmd->bindValue(":city",$params['city'],PDO::PARAM_STR);
                $cmd->bindValue(":phone1",$params['phone1'],PDO::PARAM_STR);
                $cmd->bindValue(":email1",$params['email1'],PDO::PARAM_STR);

                if($params['password'] !== "" )
                {
                    $cmd->bindValue(":password",$params['password'],PDO::PARAM_STR);
                }

                $cmd->execute();

                return true;
                
            }
            else
                return -1;
        }
        else
            return -1;
    }

    public function getProfile($params)
    {
        if(is_array($params))
        {
            if(array_key_exists('userId', $params))
            {

                $sql = "SELECT id, name, firstname, street, city, country, zip, phone1, email1 FROM hr_user WHERE id=:id";
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['userId'],PDO::PARAM_STR);
                $data = $cmd->query();
                if($data)
                {
                    $data = $data->read();

                    return $data;

                }
                else
                    return false;

            }
            else
                return -1;
        }
        else
            return -1;
        
    }

    public function login($params)
    {

        if(is_array($params))
        {
            if(array_key_exists('username', $params))
            {
                if(array_key_exists('password', $params))
                {
                    $sql = "SELECT * FROM hr_user WHERE password=:password AND email1=:username";

                    $cmd=$this->db->createCommand($sql);
                    $cmd->bindValue(":password",sha1($params['password']),PDO::PARAM_STR);
                    $cmd->bindValue(":username",$params['username'],PDO::PARAM_STR);
                    $data = $cmd->query();
                    $data = $data->readAll();

                    if(count($data)>0)
                        return $data[0]['id'];
                    else
                        return false;
                }
                else
                    return -1;
            }
            else
                return -1;
        }
        else
            return -1;
    }

    public function getAllSubscription($params)
    {
        
        if(is_array($params))
        {
            if(array_key_exists('userId', $params))
            {

                $sql = "SELECT sa.id, sa.create_date, s.description, sa.status, sa.start, sa.end, sa.credit AS creditUsed, s.credit,s.name, s.price FROM hr_vp_subscription_attribution AS sa LEFT JOIN hr_vp_subscription AS s ON s.id=sa.subcription_id WHERE sa.user_id=:id ORDER BY sa.id DESC";
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['userId'],PDO::PARAM_STR);
                $data = $cmd->query();
                if($data)
                {
                    $data = $data->readAll();

                    return $data;

                }
                else
                    return false;

            }
            else
                return -1;
        }
        else
            return -1;
    }

    public function getCurrentSubscription($params)
    {
        if(is_array($params))
        {
            if(array_key_exists('userId', $params))
            {
                $sql = "SELECT sa.create_date, s.description, sa.status, sa.start, sa.end, sa.credit AS creditUsed, s.credit,s.name, s.price FROM hr_vp_subscription_attribution AS sa LEFT JOIN hr_vp_subscription AS s ON s.id=sa.subcription_id WHERE sa.user_id=:id AND ( sa.status='started' OR sa.status='not_start') ORDER BY sa.id LIMIT 0, 1";
                $cmd=$this->db->createCommand($sql);
                $cmd->bindValue(":id",$params['userId'],PDO::PARAM_STR);
                $data = $cmd->query();
                if($data)
                {
                    $data = $data->read();

                    return $data;

                }
                else
                    return false;

            }
            else
                return -1;
        }
        else
            return -1;
    }

    public function getOccupation()
    {

        $sql = "SELECT * FROM hr_vp_parking";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        if($data)
        {
            $data = $data->readAll();

            $totalSize = 0;
            $totalFilled = 0;

            foreach($data as $d)
            {
               $totalSize+= $d['area'];
               $totalFilled+= $d['filling'];
            }

            return array('total'=>$totalSize, 'rate'=>$totalFilled);

        }
        else
            return false;

    }*/
}

?>
