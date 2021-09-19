<?php

namespace App\Service;

use DateInterval;

class MyDateInterval extends DateInterval
{
    /**
     * @param DateInterval $from
     * @return MyDateInterval
     * @throws \Exception
     */
    public static function fromDateInterval(DateInterval $from)
    {
        return new MyDateInterval($from->format('P%yY%dDT%hH%iM%sS'));
    }

    public function add(DateInterval $interval): void
    {
        foreach (str_split('ymdhis') as $prop) {
            $this->$prop += $interval->$prop;
        }
        $this->i += (int)($this->s / 60);
        $this->s %= 60;
        $this->h += (int)($this->i / 60);
        $this->i %= 60;
    }
}