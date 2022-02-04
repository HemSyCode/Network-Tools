<?php

namespace App\Controller;

use App\Service\BasicsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private BasicsService $basicsService,
    )
    {}

    #[Route('/', name: 'home')]
    public function index(): Response
    {

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/ping', name: 'ping')]
    public function ping(): Response
    {
        return $this->render('home/ping.html.twig', [
            'host' => !empty($_GET['host']) ? $_GET['host'] : '',
            'ipversion' => !empty($_GET['ipversion']) ? $_GET['ipversion'] : '',
            'count' => !empty($_GET['count']) ? $_GET['count'] : '',
        ]);
    }

    #[Route('/ping/stream', name: 'pingStream')]
    public function pingStream(): Response
    {
        header('Content-Encoding: none;');
        echo '<pre>';
        $this->basicsService->pingStream($_GET['host'], $_GET['ipversion'], $_GET['count']);
        echo '</pre>';
        return new Response('');
    }

    #[Route('/traceroute', name: 'traceroute')]
    public function traceroute(): Response
    {
        return $this->render('home/traceroute.html.twig', [
            'host' => !empty($_GET['host']) ? $_GET['host'] : '',
            'ipversion' => !empty($_GET['ipversion']) ? $_GET['ipversion'] : '',
            'maxhop' => !empty($_GET['maxhop']) ? $_GET['maxhop'] : '',
        ]);
    }

    #[Route('/traceroute/stream', name: 'tracerouteStream')]
    public function tracerouteStream(): Response
    {
        header('Content-Encoding: none;');
        echo '<pre>';
        $this->basicsService->tracerouteStream($_GET['host'], $_GET['ipversion'], $_GET['maxhop']);
        echo '</pre>';
        return new Response('');
    }

    #[Route('/nslookup', name: 'nslookup')]
    public function nslookup(): Response
    {
        $requestOptions = [
            'server' => !empty($_GET['server']) ? $_GET['server'] : '',
            'type' => !empty($_GET['type']) ? $_GET['type'] : '',
        ];

        $availableTypes = [
            'A' => 'A',
            'AAAA' => 'AAAA',
            'CNAME' => 'CNAME',
            'MX' => 'MX',
            'TXT' => 'TXT',
            'NS' => 'NS',
            'SOA' => 'SOA',
            'PTR' => 'PTR',
        ];

        // Look for empty values
        for ($i=0; $i<=(count($requestOptions)-1); $i++)
            if (empty($requestOptions[$i]))
                unset($requestOptions[$i]);

        if (!empty($_GET['host']))
        {
            $results = $this->basicsService->nslookup($_GET['host'], $requestOptions);
            if (!in_array($_GET['type'], ['A', 'AAAA', 'CNAME']))
                for ($i=0; $i<=(count($results)-1); $i++)
                    if ($results[$i]['type'] !== $_GET['type'])
                        unset($results[$i]);
        }

        return $this->render('home/nslookup.html.twig', [
            'host' => !empty($_GET['host']) ? $_GET['host'] : '',
            'server' => !empty($_GET['server']) ? $_GET['server'] : '8.8.8.8',
            'results' => !empty($results) ? $results : '',
            'availableTypes' => $availableTypes,
            'requestOptions' => $requestOptions,
        ]);
    }

    #[Route('/whois', name: 'whois')]
    public function whois(): Response
    {

        $domain = !empty($_GET['domain']) ? $_GET['domain'] : '';
        $result = !empty($domain) ? $this->basicsService->whois($domain) : '';

        return $this->render('home/whois.html.twig', [
            'domain' => $domain,
            'result' => !empty($result) ? $result : '',
        ]);
    }


}
