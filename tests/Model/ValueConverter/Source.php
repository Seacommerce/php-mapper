<?php


namespace Seacommerce\Mapper\Test\Model\ValueConverter;


class Source
{
    /** @var \DateTime|null */
    private $date;

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     * @return Source
     */
    public function setDate(?\DateTime $date): Source
    {
        $this->date = $date;
        return $this;
    }
}