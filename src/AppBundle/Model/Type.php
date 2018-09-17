<?php

namespace AppBundle\Model;

class Type {
    private $typesJsonPath;

    public function __construct($typesJsonPath)
    {
        $this->typesJsonPath = $typesJsonPath;
    }

    public function getTypes()
    {
        $data = [];
        if (file_exists($this->typesJsonPath)) {
            $data = json_decode(file_get_contents($this->typesJsonPath));
        }

        return $data;
    }
}
