<?php

abstract class TComponentModule extends TModule
{

    abstract public function cleanData($db, $userId);

    abstract public function saveData($db, $form, $userId);

    abstract public function setData($db, $form);
}

?>
