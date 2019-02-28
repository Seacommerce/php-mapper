<?php

namespace Seacommerce\Mapper\Test\Model\UnReadWritable;

class Source
{
    /** @var string|null */
    private $none;

    /** @var string|null */
    private $ro;

    /** @var string|null */
    private $wo;

    /**
     * @return string|null
     */
    public function getRo(): ?string
    {
        return $this->ro;
    }

    /**
     * @param string|null $wo
     */
    public function setWo(?string $wo): void
    {
        $this->wo = $wo;
    }
}