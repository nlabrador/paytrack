<?php

namespace AppBundle\Model;

class Session {
    private $storage;
    private $sessionId;

    public function __construct($sessionId)
    {
        $sessionId = preg_replace("/PHPSESSID=/", '', $sessionId);

        $this->storage = new GoogleStorage();
        $this->storage->setObjectName('data/sessions/'.$sessionId.'.json');

        $this->sessionId = $sessionId;
    }

    /**
     * Get session  data
     * @param session field
     */
    public function get($field)
    {
        if (!$this->sessionId) {
            return false;
        }

        $content = $this->storage->getContents(); 

        if ($content) {
            $session = json_decode($content, true);

            if (isset($session[$field])) {
                return $session[$field];
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function set($field, $value)
    {
        if (!$this->sessionId) {
            return false;
        }

        $content = $this->storage->getContents(); 

        if ($content) {
            $session = json_decode($content, true);
        }
        else {
            $session = [];
        }

        $session[$field] = $value; 

        $this->storage->saveJsonObject($session);

        return true;
    }
}
