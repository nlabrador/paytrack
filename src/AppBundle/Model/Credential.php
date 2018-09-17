<?php

namespace AppBundle\Model;

class Credential {
    private $credentialFile;

    /**
     * Construct
     * @param $credentialFile - Full path of crendentials file
     */
    public function __construct($credentialFile)
    {
        $this->credentialFile = $credentialFile;
    }

    /**
     * Find email address on the credential file
     * Returns the password key if found and false otherwise
     */
    public function find($emailAddress)
    {
        $content = file_get_contents($this->credentialFile);

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
        $content = file_get_contents($this->credentialFile);

        if ($content) {
            $credentials = json_decode($content, true);

            $credentials[$emailAddress] = $this->generatePassKey($password);
            
            file_put_contents($this->credentialFile, json_encode($credentials));

            return true;
        }
        else {
            return false;
        }
    }
}
