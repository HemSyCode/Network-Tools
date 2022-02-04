<?php

namespace App\Command;

use App\Entity\RirIpNetwork;
use App\Entity\Utilities\IpUtil;
use App\Repository\RirIpNetworkRepository;
use App\Repository\RirRepository;
use App\Service\WhoisService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScanIpsCommand extends Command
{
    protected static $defaultName = 'app:scan:ips';

    public function __construct(
        private EntityManagerInterface $em,
        private RirRepository $rirRepository,
        private RirIpNetworkRepository $rirIpNetworkRepository,
        private WhoisService $whoisService,
    )
    {
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ## Init.
        $io = new SymfonyStyle($input, $output);

        ## Text.
        $io->title('Scan IPs');

        ##
        $strTest = "
% This is the RIPE Database query service.
% The objects are in RPSL format.
%
% The RIPE Database is subject to Terms and Conditions.
% See http://www.ripe.net/db/support/db-terms-conditions.pdf

% Information related to '91.134.196.0 - 91.134.196.3'

% Abuse contact for '91.134.196.0 - 91.134.196.3' is 'contact@sylvainhemon.fr'

inetnum:        91.134.196.0 - 91.134.196.3
netname:        OVH_108593838
descr:          OVH Static IP
country:        FR
org:            ORG-HS154-RIPE
admin-c:        OTC2-RIPE
tech-c:         OTC2-RIPE
status:         ASSIGNED PA
mnt-by:         OVH-MNT
created:        2016-05-13T19:36:32Z
last-modified:  2016-05-13T19:36:32Z
source:         RIPE

organisation:   ORG-HS154-RIPE
org-name:       HEMON Sylvain
org-type:       OTHER
address:        28 Rue Park Marion
address:        56300 Saint-Thuriau
address:        FR
e-mail:         contact@sylvainhemon.fr
phone:          +33.659456193
abuse-c:        ACRO21339-RIPE
mnt-ref:        OVH-MNT
mnt-by:         OVH-MNT
created:        2016-05-13T19:36:07Z
last-modified:  2018-12-27T12:10:16Z
source:         RIPE

role:           OVH Technical Contact
address:        OVH SAS
address:        2 rue Kellermann
address:        59100 Roubaix
address:        France
e-mail:         noc@ovh.net
admin-c:        OK217-RIPE
tech-c:         GM84-RIPE
tech-c:         SL10162-RIPE
nic-hdl:        OTC2-RIPE
notify:         noc@ovh.net
abuse-mailbox:  abuse@ovh.net
mnt-by:         OVH-MNT
created:        2004-01-28T17:42:29Z
last-modified:  2014-09-05T10:47:15Z
source:         RIPE

% Information related to '91.134.0.0/16AS16276'

route:          91.134.0.0/16
origin:         AS16276
mnt-by:         OVH-MNT
created:        2016-04-15T11:43:03Z
last-modified:  2016-04-15T11:43:03Z
source:         RIPE
descr:          OVH

% This query was served by the RIPE Database Query Service version 1.102.2 (HEREFORD)

";

        $this->whoisService->rpstToArray($strTest);

        ## Text.
        $io->info("Done.");

        ## Return.
        return 0;
    }
}
