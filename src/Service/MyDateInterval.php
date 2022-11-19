<?php

namespace App\Service;

use DateInterval;
use Exception;

class MyDateInterval extends DateInterval
{
    /**
     * @param DateInterval $from
     *
     * @throws Exception
     *
     * @return MyDateInterval
     */
    public static function fromDateInterval(DateInterval $from): MyDateInterval
    {
        return new MyDateInterval($from->format('P%yY%dDT%hH%iM%sS'));
    }

    public function add(DateInterval $interval): void
    {
        foreach (str_split('ymdhis') as $prop) {
            $this->{$prop} += $interval->{$prop};
        }
        $this->i += (int) ($this->s / 60);
        $this->s %= 60;
        $this->h += (int) ($this->i / 60);
        $this->i %= 60;
    }
}
