<?php

namespace App\Service;

class BasicsService
{

    function pingStream(string $host = '127.0.0.1', int $ipVersion = 4, int $count = 5): void
    {
        $cmd =  ($this->isLinux())
            ? escapeshellcmd("ping -".strval($ipVersion)." -c ".$count." ".$host)
            : escapeshellcmd("ping -".strval($ipVersion)." -n ".$count." ".$host);

        header('Content-Encoding: none;');
        set_time_limit(0);
        echo "Command: ".$cmd."<br/>"."\r\n";

        $handle = popen($cmd, "r");
        if (ob_get_level() == 0)
            ob_start();
        while(!feof($handle)) {
            $buffer = fgets($handle);
            $buffer = trim(htmlspecialchars($buffer));
            echo $buffer . str_pad('', 4096) . "<br />";
            ob_flush();
            flush();
            sleep(1);
        }
        pclose($handle);
        ob_end_flush();
    }

    function tracerouteStream(string $host = '127.0.0.1', int $ipVersion = 4, int $maxHops = 30): void
    {
        $cmd = ($this->isLinux())
            ? escapeshellcmd("traceroute -".strval($ipVersion)." -m ".$maxHops." ".$host)
            : escapeshellcmd("tracert -".strval($ipVersion)." -h ".$maxHops." ".$host);

        header('Content-Encoding: none;');
        set_time_limit(0);
        echo "Command: ".$cmd."<br/>"."\r\n";

        $handle = popen($cmd, "r");
        if (ob_get_level() == 0)
            ob_start();
        while(!feof($handle)) {
            $buffer = fgets($handle);
            $buffer = trim(htmlspecialchars($buffer));
            echo $buffer . str_pad('', 4096) . "<br />";
            ob_flush();
            flush();
            sleep(1);
        }
        pclose($handle);
        ob_end_flush();
    }

    function nslookup(string $host = '127.0.0.1', array $options = []): array
    {
        $baseOptions = [
            'type' => 'A',
            'server' => '8.8.8.8',
        ];
        
        $EOL = "\n";

        $options = array_merge($baseOptions, $options);

        $resp = '';
        $respArray = [];
        $rowRespArray = [];
//        $response = shell_exec( escapeshellcmd("dig ".$host." ".$options['type']." @".$options['server']." +noall +answer +nocomments") );
        $response = shell_exec( escapeshellcmd("dig ".$host." ".$options['type']." @".$options['server']." +answer +nocomments") );
        if (!empty($response))
            foreach (explode($EOL, $response) as $respLine)
                if ($respLine != $EOL)
                    if (!empty($respLine))
                        if (!preg_match("#^;(.*)$#i", $respLine))
                        {
                            $rowRespArray[] = $respLine;
                            $resp .= $respLine.$EOL;
                        }

        foreach ($rowRespArray as $rowLine)
        {
            $line = preg_replace('/\t+/', "\t", $rowLine);
            $lineParts = explode("\t", $line);
            $respArray[] = [
                'domain' => $lineParts[0],
                'ttl' => $lineParts[1],
                'class' => $lineParts[2],
                'type' => $lineParts[3],
                'target' => $lineParts[4],
            ];

        }

        return $respArray;
    }

    function whois(string $domain = 'example.com'): string
    {
        $result = shell_exec( escapeshellcmd("whois ".$domain) );
        return !empty($result) ? $result : 'no result';
    }

    public function isLinux(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? false : true;
    }

    public function removeAnyWhiteSpace(string $string): string{
        return preg_replace('/\s+/', '', $string);
    }

}