<?php
namespace App\Twig;

use App\Entity\Backoffice\Ticket;
use App\Entity\Setting;
use App\Repository\Backoffice\TicketRepository;
use App\Service\Backoffice\SmsService;
use App\Service\BasicsService;
use App\Service\Misc\FileDbService;
use App\Service\OptionsMain;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use stdClass;
use Symfony\Component\Intl\Locales;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private BasicsService $basicsService,
    ){}

    public function getFilters(): array
    {
        return [
            new TwigFilter('json_decode', [$this, 'jsonDecode']),
        ];
    }

    public function getFunctions(): array
    {
        return [
//            new TwigFunction('traceroute2', [$this, 'traceroute2']),
        ];
    }


    public function jsonDecode($str)
    {
        return json_decode($str);
    }
}