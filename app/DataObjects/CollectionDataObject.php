<?php

namespace App\DataObjects;

/**
 * Class CollectionDataObject
 *
 * @package App\DataObjects
 */
class CollectionDataObject extends BaseDataObject
{
    protected $items;
    protected $key = 'items';

    public function __construct($items = [], $key = null)
    {
        if ($items instanceof \Traversable) {
            $this->items = $items;
        } else {
            $this->items = [];
        }
    }

    /**
     * Set the collection key
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Add a new serializable
     *
     * @param Serializable $item
     *
     * @return $this
     */
    public function add(Serializable $item)
    {
        array_push($this->items, $item);

        return $this;
    }

    /** {@inheritDoc} */
    public function serializeMember()
    {
        return [
            'count'    => count($this->items),
            $this->key => $this->items
        ];
    }
}