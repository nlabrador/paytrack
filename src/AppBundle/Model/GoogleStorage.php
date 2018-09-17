<?php

namespace AppBundle\Model;

use Google\Cloud\Storage\StorageClient;

class GoogleStorage {
    const GCP_PROJECT_ID = 'paytrackph18';
    const GCP_BUCKET_NAME = 'paytrackph18.appspot.com';

    private $storage;
    private $objName;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->storage = new StorageClient([
            'projectId' => self::GCP_PROJECT_ID
        ]);
    }

    public function setObjectName($objName)
    {
        $this->objName = $objName;
    }

    public function getContents()
    {
        $bucketName = self::GCP_BUCKET_NAME;
        $objectName = $this->objName;

        $bucket = $this->storage->bucket($bucketName);
        $object = $bucket->object($objectName);
        $content = $object->exists() ? $object->downloadAsString() : '';

        return $content;
    }

    public function saveJsonObject($data)
    {
        $bucketName = self::GCP_BUCKET_NAME;
        $objectName = $this->objName;

        $this->storage->bucket($bucketName)->upload(
            json_encode($data),
            [
                'name'      => $objectName,
                'metadata'  => ['contentType' => 'text/json']
            ]
        );
    }

    public function getObjects()
    {
        $bucketName = self::GCP_BUCKET_NAME;
        
        return $this->storage->bucket($bucketName)->objects();
    }
}
