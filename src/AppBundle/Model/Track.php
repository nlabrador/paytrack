<?php

namespace AppBundle\Model;

use Symfony\Component\Finder\Finder;

class Track {
    private $tracksJsonPath;

    public function __construct($tracksJsonPath)
    {
        $this->tracksJsonPath = $tracksJsonPath;
    }

    public function getTracks()
    {
        $data = [];
        if (file_exists($this->tracksJsonPath)) {
            $data = json_decode(file_get_contents($this->tracksJsonPath));
        }

        return $data;
    }

    public function saveTrack($data)
    {
        file_put_contents($this->tracksJsonPath, json_encode($data));
    }

    public function getAllTracks()
    {
        $tracksDir = preg_replace("/\/\d{4}-.*$/", "", $this->tracksJsonPath);
        $tracks = [];

        $finder = new Finder();
        $finder->files()->in($tracksDir);

        foreach ($finder as $file) {
            $data = json_decode($file->getContents());

            foreach ($data as $item) {
                $tracks[] = $item;
            }
        }

        return $tracks;
    }
}
