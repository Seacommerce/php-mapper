<?php


namespace Seacommerce\Mapper\Test\Model\ValueConverter;


class Target
{
    /** @var \DateTimeImmutable|null */
    private $date;

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
}