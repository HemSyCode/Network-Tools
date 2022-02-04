<?php

namespace App\Service;

use App\Entity\Rir;
use App\Entity\RirAsn;
use App\Entity\RirIpNetwork;
use App\Entity\Utilities\IpUtil;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class WhoisService
{

    const EOL = "\n";

    public function __construct(
        private EntityManagerInterface $em,
        private BasicsService $basicsService,
    )
    {}





    public function rpstToArray(string $str) : array
    {
        ## Init variables.
        $eol = "\r\n";

        ## Convert string to an array, each string line is a array line.
        $array = explode($eol, $str);

        ## Remove comments.
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


        ## Parts the elements, each "section" is in its own array.
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

//        dump($array[0]);

        $newArray = [];
        $lastKey = null;
        foreach ($array as $partArrayKey => $partArrayVal)
        {
            foreach ($partArrayVal as $lineKey => $lineVal)
            {
                $parts = [];
//                $tok = strtok($lineVal, ":");
//                while ($tok !== false) {
//                    $parts[] = $tok;
//                    $tok = strtok(":");
//                }
                $parts = preg_split("#(: )|(:)$#i", $lineVal);

//                dump($parts);

                if (count($parts) >= 2)
                    $lastKey = $parts[0];

                if (count($parts) === 1)
                    array_unshift($parts, $lastKey);


//                if (!empty($parts[0]) && !empty($parts[1]))
                if (!empty($parts[0]))
                    if (empty($newArray[$partArrayKey][trim($parts[0])]))
                    {
                        $newArray[$partArrayKey][trim($parts[0])] = trim($parts[1]);
                    } elseif (is_string($newArray[$partArrayKey][trim($parts[0])])) {
                        $newArray[$partArrayKey][trim($parts[0])] = array($newArray[$partArrayKey][trim($parts[0])]);
                        $newArray[$partArrayKey][trim($parts[0])][] = trim($parts[1]);
                    } else {
                        $newArray[$partArrayKey][trim($parts[0])][] = trim($parts[1]);
                    }
            }
            $lastKey = null;
        }

        dd($newArray);

        return $newArray;
    }



    public function rowDataRirAllocation2RirIpNetwork(Rir $rir, RirIpNetwork &$rirIpNetwork, array $parts): bool
    {

        $ipVersion = intval(str_replace('ipv', '', strtolower($parts[2])));

        ## Which ip version is?
        if ($ipVersion === 4)
            $cidrIpCount = IpUtil::IPv4cidrIpCount();
        elseif ($ipVersion === 6)
            $cidrIpCount = IpUtil::IPv6cidrIpCount();
        else
            throw new Exception('Not valid IP address.');

        # Since some RIRs allocate random non CIDR addresses
        # We shall split them up into the best CIDR that we can
        # Really unhappy with this :/
        $roundedCidr = 32 - intval(log($parts[4]) / log(2));
        $roundedAmount = pow(2, (32 - $roundedCidr));
        $cidr = ($ipVersion === 4) ? array_search($roundedAmount, $cidrIpCount) : $parts[4];
        $ipCount = ($ipVersion === 4) ? $parts[4] : array_search($parts[4], array_flip($cidrIpCount));

        ## Populate IpUtil.
        $newIp = (new IpUtil());
        $newIp->setIp($parts[3], $cidr);

        ## Replace 'ZZ' with null country code
        $parts[1] = (strtoupper($parts[1]) === 'ZZ') ? null : $parts[1];

        ##
        if ($parts[5] === '00000000' or empty($parts[5]))
            $parts[5] = '19700101';
        $allocatedAt = DateTimeImmutable::createFromFormat('Ymd', $parts[5]);
        if (!$allocatedAt instanceof DateTimeImmutable)
            $allocatedAt = new DateTimeImmutable('1970-01-01 00:00:00');

        ## Populate object.
        $rirIpNetwork
            ->setHandle($rir->getCode()."_".$newIp->getNetwork()."/".$newIp->getCidr())
            ->setRir($rir)
            ->setIpVersion($ipVersion)
            ->setCountry(strtoupper($parts[1]))
            ->setStatus(strtoupper($parts[6]))
            ->setAllocatedAt($allocatedAt)
            ->setIpStart($newIp->getNetwork())
            ->setIpEnd($newIp->getBroadcast())
            ->setCidr($newIp->getCidr())
            ->setIpCount($ipCount)
            ->setIpStartDec(IpUtil::ip2dec($newIp->getNetwork()))
            ->setIpEndDec(IpUtil::ip2dec($newIp->getBroadcast()));

        return true;
    }

    public function rowDataRirAllocation2RirAsn(Rir $rir, RirAsn &$rirAsn, array $parts): bool
    {
        ## Replace 'ZZ' with null country code
        $parts[1] = (strtoupper($parts[1]) === 'ZZ') ? null : $parts[1];

        ##
        if ($parts[5] === '00000000' or empty($parts[5]))
            $parts[5] = '19700101';
        $allocatedAt = DateTimeImmutable::createFromFormat('Ymd', $parts[5]);
        if (!$allocatedAt instanceof DateTimeImmutable)
            $allocatedAt = new DateTimeImmutable('1970-01-01 00:00:00');

        ## Populate object.
        $rirAsn
            ->setHandle($rir->getCode()."_".$parts[3]."/".$parts[4])
            ->setRir($rir)
            ->setCountry(strtoupper($parts[1]))
            ->setStatus(strtoupper($parts[6]))
            ->setAllocatedAt($allocatedAt)
            ->setAsnCount(intval($parts[4]))
            ->setAsnStart(intval($parts[3]))
            ->setAsnEnd($rirAsn->getAsnStart() + ($rirAsn->getAsnCount() - 1))
        ;

        return true;
    }

    public function updateRirIpNetworkObject(RirIpNetwork &$source, RirIpNetwork &$target) : void
    {
        $target
            ->setHandle($source->getHandle())
            ->setRir($source->getRir())
            ->setIpVersion($source->getIpVersion())
            ->setCountry($source->getCountry())
            ->setStatus($source->getStatus())
            ->setAllocatedAt($source->getAllocatedAt())
            ->setUpdatedAt($source->getUpdatedAt())
            ->setIpStart($source->getIpStart())
            ->setIpEnd($source->getIpEnd())
            ->setCidr($source->getCidr())
            ->setIpCount($source->getIpCount())
            ->setIpStartDec($source->getIpStartDec())
            ->setIpEndDec($source->getIpEndDec())
            ;
    }

    public function updateRirAsnObject(RirAsn &$source, RirAsn &$target) : void
    {
        $target
            ->setHandle($source->getHandle())
            ->setRir($source->getRir())
            ->setCountry($source->getCountry())
            ->setStatus($source->getStatus())
            ->setAllocatedAt($source->getAllocatedAt())
            ->setUpdatedAt($source->getUpdatedAt())
            ->setAsnStart($source->getAsnStart())
            ->setAsnEnd($source->getAsnEnd())
            ->setAsnCount($source->getAsnCount())
            ;
    }
}