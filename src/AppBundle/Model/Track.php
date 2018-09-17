<?php

namespace AppBundle\Model;

class Track {
    private $storage;
    private $tracksJsonPath;

    public function __construct($tracksJsonPath)
    {
        $this->storage = new GoogleStorage();
        $this->storage->setObjectName($tracksJsonPath);
        $this->tracksJsonPath = $tracksJsonPath;
    }

    public function getTracks()
    {
        $data = [];
        
        $contents = $this->storage->getContents();
        if ($contents) {
            $data = json_decode($contents);
        }

        return $data;
    }

    public function saveTrack($data)
    {
        $this->storage->saveJsonObject($data);
    }

    public function getAllTracks()
    {
        $tracksDir = preg_replace("/\/\d{4}-.*$/", "", $this->tracksJsonPath);
        $tracksDir = preg_replace("/^.*tracks\//", "", $tracksDir);

        $tracks = [];
        foreach ($this->storage->getObjects() as $object) {
            if (preg_match("/$tracksDir/", $object->name())) {
                $data = json_decode($object->downloadAsString());

                foreach ($data as $item) {
                    $tracks[] = $item;
                }
            }
        }

        return $tracks;
    }
}
