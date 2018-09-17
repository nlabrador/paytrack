<?php

namespace AppBundle\Model;

class Type {
    private $storage;

    public function __construct()
    {
        $this->storage = new GoogleStorage();
        $this->storage->setObjectName('data/types.json');
    }

    public function getTypes()
    {
        $data = [];
        $contents = $this->storage->getContents();
        if ($contents) {
            $data = json_decode($contents);
        }

        return $data;
    }
}
