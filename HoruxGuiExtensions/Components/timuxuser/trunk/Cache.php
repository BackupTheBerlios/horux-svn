<?php


Prado::using("System.Caching.TMemCache");

class Cache extends TCache {

    public $cache = array();

    protected function getValue($key) {
        return $this->cache[$key];
    }

    protected function setValue($key,$value,$expire) {
        $this->cache[$key] = $value;
    }

    protected function addValue($key,$value,$expire) {
        $this->cache[$key] = $value;
    }

    protected function deleteValue($key) {
        unset($this->cache[$key]);
    }

    public function flush() {
        $this->cache = array();
    }
}

?>
