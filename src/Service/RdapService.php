<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class RdapService
{

    const EOL = "\n";

    public function __construct(
        private EntityManagerInterface $em,
        private BasicsService $basicsService,
    )
    {}



    public function scanIpWhois()
    {
        $response = '';


        dd($this->rpstToArray($response));
    }

    public function rpstToArray(string $str) : array
    {
        $eol = "\r\n";

        $array = explode($eol, $str);

        ## Unset comments.
        foreach ($array as $lineKey => $lineVal)
        {
            if (preg_match('#^%#i', $lineVal))
                unset($array[$lineKey]);
            if (preg_match('/^#/i', $lineVal))
                unset($array[$lineKey]);
        }

        ## Rewrite array keys.
        $newArray = [];
        foreach ($array as $lineKey => $lineVal)
            $newArray[] = $lineVal;
        $array = $newArray;

        ## Remove first AND last AND duplicate empty line.
        $i=0;
        foreach ($array as $lineKey => $lineVal)
        {
            if (array_key_exists($i+1, $array))
                if ($array[$i] === '' && $array[$i+1] === '')
                    unset($array[$i]);
            $i++;
        }
        $firstKey = array_key_first($array);
        $lastKey = array_key_last($array);
        if ($array[$firstKey] === '')
            unset($array[$firstKey]);
        if ($array[$lastKey] === '')
            unset($array[$lastKey]);

        ## Parts elements.
        $i=0;
        $newArray = [];
        foreach ($array as $lineKey => $lineVal)
        {
            if ($array[$lineKey] === '')
                $i++;
            if ($array[$lineKey] !== '')
                $newArray[$i][] = $array[$lineKey];
        }
        $array = $newArray;

        $newArray = [];
        foreach ($array as $partArrayKey => $partArrayVal)
        {
            foreach ($partArrayVal as $lineKey => $lineVal)
            {
                $parts = [];
                $tok = strtok($lineVal, ":");
                while ($tok !== false) {
                    $parts[] = $tok;
                    $tok = strtok(":");
                }
                if (!empty($parts[0]) && !empty($parts[1]))
                    $newArray[$partArrayKey][trim($parts[0])] = trim($parts[1]);
            }
        }

        return $newArray;
    }
}