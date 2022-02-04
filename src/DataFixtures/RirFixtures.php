<?php

namespace App\DataFixtures;

use App\Entity\Rir;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RirFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
//        ## IANA
//        $rir = (new Rir())
//            ->setCode('IANA')
//            ->setName('IANA')
//            ->setFullName('Internet Assigned Numbers Authority')
//            ->setWebsite('iana.org')
//            ->setWhoisServer('')
//            ->setRadpServerUrl('')
//            ->setAllocationListUrl('https://ftp.apnic.net/stats/iana/delegated-iana-latest')
//        ;
//        $manager->persist($rir);

        ## RIPE
        $rir = (new Rir())
            ->setCode('RIPENCC')
            ->setName('RIPE')
            ->setFullName('Réseaux IP Européens Network Coordination Centre')
            ->setWebsite('ripe.net')
            ->setWhoisServer('whois.ripe.net')
            ->setRadpServerUrl('https://rdap.db.ripe.net/')
            ->setAllocationListUrl('https://ftp.ripe.net/ripe/stats/delegated-ripencc-extended-latest')
        ;
        $manager->persist($rir);

        ## ARIN
        $rir = (new Rir())
            ->setCode('ARIN')
            ->setName('ARIN')
            ->setFullName('American Registry for Internet Numbers')
            ->setWebsite('arin.net')
            ->setWhoisServer('whois.arin.net')
            ->setRadpServerUrl('https://rdap.arin.net/registry/')
            ->setAllocationListUrl('https://ftp.arin.net/pub/stats/arin/delegated-arin-extended-latest')
        ;
        $manager->persist($rir);

        ## APNIC
        $rir = (new Rir())
            ->setCode('APNIC')
            ->setName('APNIC')
            ->setFullName('Asia-Pacific Network Information Centre')
            ->setWebsite('apnic.net')
            ->setWhoisServer('whois.apnic.net')
            ->setRadpServerUrl('https://rdap.apnic.net/')
            ->setAllocationListUrl('https://ftp.apnic.net/pub/stats/apnic/delegated-apnic-extended-latest')
        ;
        $manager->persist($rir);

        ## LACNIC
        $rir = (new Rir())
            ->setCode('LACNIC')
            ->setName('LacNIC')
            ->setFullName('Latin America and Caribbean Network Information Centre')
            ->setWebsite('lacnic.net')
            ->setWhoisServer('whois.lacnic.net')
            ->setRadpServerUrl('https://rdap.lacnic.net/rdap/')
            ->setAllocationListUrl('https://ftp.lacnic.net/pub/stats/lacnic/delegated-lacnic-extended-latest')
        ;
        $manager->persist($rir);

        ## AFRINIC
        $rir = (new Rir())
            ->setCode('AFRINIC')
            ->setName('AfriNIC')
            ->setFullName('African Network Information Center')
            ->setWebsite('afrinic.net')
            ->setWhoisServer('whois.afrinic.net')
            ->setRadpServerUrl('https://rdap.afrinic.net/rdap/')
            ->setAllocationListUrl('https://ftp.afrinic.net/stats/afrinic/delegated-afrinic-extended-latest')
        ;
        $manager->persist($rir);

        $manager->flush();
    }
}
