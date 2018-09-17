<?php

namespace AppBundle\Model;

class Credential {
    private $storage;

    public function __construct()
    {
        $this->storage = new GoogleStorage();
        $this->storage->setObjectName('data/credential.json');
    }

    /**
     * Find email address on the credential file
     * Returns the password key if found and false otherwise
     */
    public function find($emailAddress)
    {
        $content = $this->storage->getContents(); 

        if ($content) {
            $credentials = json_decode($content, true);

            if (isset($credentials[$emailAddress])) {
                return $credentials[$emailAddress];
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function validate($emailAddress, $password)
    {
        $foundPass = $this->find($emailAddress);

        if ($foundPass && $foundPass == $password) {
            return true;
        }
        else {
            return false;
        }
    }

    public function generatePassKey($password)
    {
        return md5($password);
    }

    public function create($emailAddress, $password)
    {
        $content = $this->storage->getContents(); 

        if ($content) {
            $credentials = json_decode($content, true);

            $credentials[$emailAddress] = $this->generatePassKey($password);

            $this->storage->saveJsonObject($credentials);

            return true;
        }
        else {
            return false;
        }
    }
}
