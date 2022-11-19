<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DateDifferenceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            //new TwigFilter('date_difference', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('date_difference', [$this, 'dateDifference']),
        ];
    }

    /**
     * @param $start
     * @param $end
     *
     * @return string
     */
    public function dateDifference($start, $end): string
    {
        if ($start instanceof \DateTimeInterface && $end instanceof \DateTimeInterface) {
            $diff = $end->diff($start);
            $days = $diff->d;

            return ($days > 0 ? $days.' d ' : '').$diff->format('%H:%I:%S');
        }

        return '-';
    }
}
