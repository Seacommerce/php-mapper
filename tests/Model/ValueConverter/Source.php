<?php


namespace Seacommerce\Mapper\Test\Model\ValueConverter;


class Source
{
    /** @var \DateTime */
    private $date;

    /** @var int */
    private $time;

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return Source
     */
    public function setDate(\DateTime $date): Source
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @param int $time
     * @return Source
     */
    public function setTime(int $time): Source
    {
        $this->time = $time;
        return $this;
    }
}