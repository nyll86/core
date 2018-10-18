<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 18.10.2018
 * Time: 23:38
 */

namespace Kernel\Core\Model\Mongo;

use MongoDB\Collection;
use MongoDB\Model;

/**
 * builder mongo
 *
 * Class Builder
 * @package Kernel\Core\Model\Mongo
 */
class Builder
{
    /**
     * collection mongo
     *
     * @var Collection
     */
    private $collection;

    /**
     * local cache
     *
     * @var array
     */
    private $cache = [];

    /**
     * Builder constructor.
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->setCollection($collection);
    }

    /**
     * get collection
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * find by id in DB
     *
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        // if already upload in cache
        if($this->inCache($id) === true) {

            // unload cache
            return $this->unloadFromCache($id);
        }

        // get result
        /** @var Model\BSONDocument $doc */
        $doc = $this->getCollection()
            ->findOne(['_id' => $id]);

        // modify to array
        $result = $this->toArray($doc);

        // upload in cache
        $this->uploadInCache($id, $result);

        return $result;
    }

    /**
     * reduction to array
     *
     * @param Model\BSONDocument|Model\BSONArray $document
     * @return array
     */
    private function toArray($document): array
    {
        $data = $document->getArrayCopy();
        if (\is_array($data)) {
            foreach ($data as &$item) {
                if ($item instanceof Model\BSONDocument || $item instanceof Model\BSONArray) {
                    $item = $this->toArray($item);
                }
            }
        }
        return $data;
    }

    /**
     * set collection mongo
     *
     * @param Collection $collection
     */
    private function setCollection(Collection $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * check cache
     *
     * @param int $id
     * @return bool
     */
    private function inCache(int $id): bool
    {
        return isset($this->cache[$id]);
    }

    /**
     * unload from cache
     *
     * @param $id
     * @return array
     */
    private function unloadFromCache($id): array
    {
        return $this->cache[$id];
    }

    /**
     * upload in cache
     *
     * @param int $id
     * @param array $data
     */
    private function uploadInCache(int $id, array $data): void
    {
        $this->cache[$id] = $data;
    }

}