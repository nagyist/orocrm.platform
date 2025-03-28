<?php

namespace Oro\Bundle\EntityBundle\EntityProperty;

interface UpdatedAtAwareInterface
{
    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime|null $updatedAt
     * @return mixed
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * @return bool
     */
    public function isUpdatedAtSet();
}
