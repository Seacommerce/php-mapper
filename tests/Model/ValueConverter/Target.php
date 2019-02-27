<?php


namespace Seacommerce\Mapper\Test\Model\ValueConverter;


use DateTime;

class Target
{
    /** @var \DateTimeImmutable|null */
    private $date;

    /** @var DateTime|null */
    private $time;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable|null $date
     * @return Target
     */
    public function setDate(?\DateTimeImmutable $date): Target
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getTime(): ?DateTime
    {
        return $this->time;
    }

    /**
     * @param DateTime|null $time
     * @return Target
     */
    public function setTime(?DateTime $time): Target
    {
        $this->time = $time;
        return $this;
    }
}