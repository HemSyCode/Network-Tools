<?php

namespace App\Command;

use App\Entity\RirAsn;
use App\Entity\RirIpNetwork;
use App\Repository\RirAsnRepository;
use App\Repository\RirIpNetworkRepository;
use App\Repository\RirRepository;
use App\Service\WhoisService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScanRirsAllocationsCommand extends Command
{
    protected static $defaultName = 'app:scan:rirallocations';

    public function __construct(
        private EntityManagerInterface $em,
        private WhoisService $whoisService,
        private RirRepository $rirRepository,
        private RirIpNetworkRepository $rirIpNetworkRepository,
        private RirAsnRepository $rirAsnRepository,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, '(IP version | ASN) or RIR - multiple values must be comma separated.')
            ->addArgument('arg2', InputArgument::OPTIONAL, '(IP version | ASN) or RIR - multiple values must be comma separated.')
        ;
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
        $arguments[0] = !empty($input->getArgument('arg1')) ? $input->getArgument('arg1') : '';
        $arguments[1] = !empty($input->getArgument('arg2')) ? $input->getArgument('arg2') : '';
        $argument = $arguments[0]." ".$arguments[1];

        ## Text.
        $io->title('RIRs allocations');

        ## Help text
        if (preg_match('#help#i', $argument))
        {
            $io->section('Help');
            $io->text("Example:");
            $io->text("  - Get allocations from RIPE database, only IPv4");
            $io->text("     - app:scan:rir RIPE 4");
            $io->text("     - app:scan:rir 4 RIPE");
            $io->text("  - Get allocations from APNIC database, ASN + IPv4");
            $io->text("     - app:scan:rir APNIC ASN,4");
            $io->text("  - Get allocations from APNIC and AFRINIC and ARIN database, IPv4 + IPv6");
            $io->text("     - app:scan:rir APNIC,AFRINIC,ARIN 4,6");
            $io->text("  - Get allocations from all database, only IPv4");
            $io->text("     - app:scan:rir 4");
            return 0;
        }

        ## Find RIRs.
        $rirs = $this->rirRepository->findAll();

        ## Handle argument.
        ## RIRs, ASN, IPv4 and IPv6.
        $desiredRirs = [];
        $desiredTargets = [];
        foreach ($rirs as $rir)
            if (preg_match('#'.$rir->getCode().'#i', $argument) OR preg_match('#'.$rir->getName().'#i', $argument))
                $desiredRirs[] = $rir;
        if (preg_match('#ASN#i', $argument))
            $desiredTargets[] = 'ASN';
        if (preg_match('#4#i', $argument))
            $desiredTargets[] = '4';
        if (preg_match('#6#i', $argument))
            $desiredTargets[] = '6';

        ## Define RIRs and (IP version, ASN).
        $rirs = (!empty($desiredRirs)) ? $desiredRirs : $rirs;
        $targets = (!empty($desiredTargets)) ? $desiredTargets : ['4', '6', 'ASN'];

        ## Loop on each RIRs.
        foreach ($rirs as $rir)
        {
            ## Init vars.
            $fileSortedObjectsByTypes = [
                'ipv4' => [],
                'ipv6' => [],
                'asn' => [],
            ];
            $dbSortedObjectsByTypes = [
                'ipv4' => [],
                'ipv6' => [],
                'asn' => [],
            ];

            ## Text.
            $io->section($rir->getName().": ".$rir->getFullName());

            ## Define Regular Expresion.
            $regex1 = '/^#/i';
            $regex2 = '#\|'.strtolower($rir->getCode()).'\|#i';
            $regex3 = '#\|summary#i';

            ## Try to get content.
            $io->comment('Downloading data...');
            try {
                $response = file_get_contents($rir->getAllocationListUrl());
            } catch (Exception $exception) {
                dump($exception->getMessage());
            }

            ## Explode content.
            $content = explode("\n", $response);

            ## Count - Display purpose.
            $ipv4NetwCount = 0;
            $ipv6NetwCount = 0;
            $asnCount = 0;
            foreach ($content as $line) {
                if (in_array('4', $targets))
                    if (preg_match('#^' . $rir->getCode() . '\|\*\|ipv4\|#i', $line))
                        $ipv4NetwCount = intval(explode('|', $line)[4]);
                if (in_array('6', $targets))
                    if (preg_match('#^' . $rir->getCode() . '\|\*\|ipv6\|#i', $line))
                        $ipv6NetwCount = intval(explode('|', $line)[4]);
                if (in_array('ASN', $targets))
                    if (preg_match('#^' . $rir->getCode() . '\|\*\|asn\|#i', $line))
                        $asnCount = intval(explode('|', $line)[4]);
            }
            if (in_array('4', $targets))
                $io->text("IPv4: ".$ipv4NetwCount." networks found.");
            if (in_array('6', $targets))
                $io->text("IPv6: ".$ipv6NetwCount." networks found.");
            if (in_array('ASN', $targets))
                $io->text("ASN: ".$asnCount." entry found.");

            ## Text
            $io->comment('Sorting data from file...');

            ## Progress bar.
            $progressBar = new ProgressBar($output, ($ipv4NetwCount + $ipv6NetwCount + $asnCount));
            $progressBar->start();

            ## Loop on each file lines.
            $countLoop = 0;
            foreach ($content as $line)
            {
                if (
                    !preg_match($regex1, $line) AND
                    !preg_match($regex2, $line) AND
                    !preg_match($regex3, $line)
                ){
                    ## IPv4 + IPv6.
                    if (in_array('4', $targets))
                    {
                        if (preg_match('#\|ipv4\|#i', $line))
                        {
                            ## Progress Bar
                            $progressBar->advance();

                            ## Get Parts
                            $rirIpNetwork = new RirIpNetwork();
                            $this->whoisService->rowDataRirAllocation2RirIpNetwork($rir, $rirIpNetwork, explode('|', $line));
                            $fileSortedObjectsByTypes['ipv4'][$rirIpNetwork->getHandle()] = $rirIpNetwork;
                        }
                    }

                    ## IPv6.
                    if (in_array('6', $targets))
                    {
                        if (preg_match('#\|ipv6\|#i', $line))
                        {
                            ## Progress Bar
                            $progressBar->advance();

                            ## Get Parts.
                            $rirIpNetwork = new RirIpNetwork();
                            $this->whoisService->rowDataRirAllocation2RirIpNetwork($rir, $rirIpNetwork, explode('|', $line));
                            $fileSortedObjectsByTypes['ipv6'][$rirIpNetwork->getHandle()] = $rirIpNetwork;
                        }
                    }

                    ## ASNs.
                    if (in_array('ASN', $targets))
                    {
                        if (preg_match('#\|asn\|#i', $line)) {
                            ## Progress Bar.
                            $progressBar->advance();

                            ## Get Parts.
                            $rirAsn = new RirAsn();
                            $this->whoisService->rowDataRirAllocation2RirAsn($rir, $rirAsn, explode('|', $line));
                            $fileSortedObjectsByTypes['asn'][$rirAsn->getHandle()] = $rirAsn;
                        }
                    }
                }
            }

            ## Progress Bar.
            $progressBar->finish();





            ## init Var.
            $step = 50;

            ## Text
            $io->text('.');
            $io->comment('Getting data from database...');

            ## Progress bar.
            $progressBar = new ProgressBar($output, ($ipv4NetwCount + $ipv6NetwCount + $asnCount));
            $progressBar->start();

            ## Loop on each sorted object.
            foreach ($fileSortedObjectsByTypes as $fileObjectsByTypeKey => $fileObjectsByType)
            {

                    $fileObjectsByTypeChunked = array_chunk($fileObjectsByType, $step, true);
                    foreach ($fileObjectsByTypeChunked as $fileObjectsByTypeChunkedParts)
                    {
                        ##
                        if ($fileObjectsByTypeKey === 'ipv4' OR $fileObjectsByTypeKey === 'ipv6')
                            $dbSortedObjectsByTypes[$fileObjectsByTypeKey] = array_merge($dbSortedObjectsByTypes[$fileObjectsByTypeKey], $this->rirIpNetworkRepository->getObjectsByHandles($fileObjectsByTypeChunkedParts));
                        elseif ($fileObjectsByTypeKey === 'asn')
                            $dbSortedObjectsByTypes[$fileObjectsByTypeKey] = array_merge($dbSortedObjectsByTypes[$fileObjectsByTypeKey], $this->rirAsnRepository->getObjectsByHandles($fileObjectsByTypeChunkedParts));

                            ## Progress Bar.
                        $progressBar->advance($step);
                    }

            }

            ## Progress Bar.
            $progressBar->finish();


            ## Text
            $io->text('.');
            $io->comment('Sorting data from database...');

            ## Progress bar.
            $progressBar = new ProgressBar($output, ($ipv4NetwCount + $ipv6NetwCount + $asnCount));
            $progressBar->start();

            ## Loop on each sorted object.
            foreach ($dbSortedObjectsByTypes as $dbObjectsByTypeKey => $dbObjectsByType)
            {
                if ($dbObjectsByTypeKey === 'ipv4' OR $dbObjectsByTypeKey === 'ipv6')
                {
                    /**
                     * @var int $rirIpNetworkKey
                     * @var RirIpNetwork $rirIpNetwork
                     */
                    foreach ($dbObjectsByType as $rirIpNetworkKey => $rirIpNetwork)
                    {
                        ##
                        $dbSortedObjectsByTypes[$dbObjectsByTypeKey][$rirIpNetwork->getHandle()] = $dbSortedObjectsByTypes[$dbObjectsByTypeKey][$rirIpNetworkKey];
                        unset($dbSortedObjectsByTypes[$dbObjectsByTypeKey][$rirIpNetworkKey]);

                        ## Progress Bar.
                        $progressBar->advance(1);
                    }
                }
                if ($dbObjectsByTypeKey === 'asn')
                {
                    /**
                     * @var int $rirAsnKey
                     * @var RirAsn $rirAsn
                     */
                    foreach ($dbObjectsByType as $rirAsnKey => $rirAsn)
                    {
                        ##
                        $dbSortedObjectsByTypes[$dbObjectsByTypeKey][$rirAsn->getHandle()] = $dbSortedObjectsByTypes[$dbObjectsByTypeKey][$rirAsnKey];
                        unset($dbSortedObjectsByTypes[$dbObjectsByTypeKey][$rirAsnKey]);

                        ## Progress Bar.
                        $progressBar->advance(1);
                    }
                }
            }

            ## Progress Bar.
            $progressBar->finish();






            ### Comparison: File => Database ###
            ### Create and Update ###

            ## Text
            $io->text('.');
            $io->comment('Creating/Updating data...');

            ## Progress bar.
            $progressBar = new ProgressBar($output, ($ipv4NetwCount + $ipv6NetwCount + $asnCount));
            $progressBar->start();

            ## Loop on each sorted object.
            $countLoop = 0;
            foreach ($fileSortedObjectsByTypes as $fileObjectsByTypeKey => $fileObjectsByType)
            {
                foreach ($fileObjectsByType as $fileRirObject)
                {
                    if ($fileObjectsByTypeKey === 'ipv4' OR $fileObjectsByTypeKey === 'ipv6')
                    {
                        /** @var RirIpNetwork $fileRirIpNetwork */
                        $fileRirIpNetwork = $fileRirObject;

                        /** @var RirIpNetwork $dbRirIpNetwork */
                        if (isset($dbSortedObjectsByTypes[$fileObjectsByTypeKey][$fileRirIpNetwork->getHandle()]))
                            $dbRirIpNetwork = $dbSortedObjectsByTypes[$fileObjectsByTypeKey][$fileRirIpNetwork->getHandle()];
                        else
                            $dbRirIpNetwork = new RirIpNetwork();

                        if (
                            $dbRirIpNetwork->getRir() !== $fileRirIpNetwork->getRir() or
                            $dbRirIpNetwork->getIpVersion() !== $fileRirIpNetwork->getIpVersion() or
                            $dbRirIpNetwork->getCountry() !== $fileRirIpNetwork->getCountry() or
                            $dbRirIpNetwork->getStatus() !== $fileRirIpNetwork->getStatus() or
                            $dbRirIpNetwork->getAllocatedAt()->format('Y-m-d') !== $fileRirIpNetwork->getAllocatedAt()->format('Y-m-d') or
                            $dbRirIpNetwork->getIpStart() !== $fileRirIpNetwork->getIpStart() or
                            $dbRirIpNetwork->getIpEnd() !== $fileRirIpNetwork->getIpEnd() or
                            $dbRirIpNetwork->getCidr() !== $fileRirIpNetwork->getCidr() or
                            $dbRirIpNetwork->getIpCount() !== $fileRirIpNetwork->getIpCount() or
                            $dbRirIpNetwork->getIpStartDec() !== $fileRirIpNetwork->getIpStartDec() or
                            $dbRirIpNetwork->getIpEndDec() !== $fileRirIpNetwork->getIpEndDec()
                        ) {

                            $this->whoisService->updateRirIpNetworkObject($fileRirIpNetwork, $dbRirIpNetwork);

                            ##
                            $this->em->persist($dbRirIpNetwork);
                        }

                        ##
                        $this->em->detach($fileRirIpNetwork);
                    }
                    elseif ($fileObjectsByTypeKey === 'asn')
                    {

                        /** @var RirAsn $fileRirAsn */
                        $fileRirAsn = $fileRirObject;

                        /** @var RirAsn $dbRirAsn */
                        if (isset($dbSortedObjectsByTypes[$fileObjectsByTypeKey][$fileRirAsn->getHandle()]))
                            $dbRirAsn = $dbSortedObjectsByTypes[$fileObjectsByTypeKey][$fileRirAsn->getHandle()];
                        else
                            $dbRirAsn = new RirAsn();

                        if (
                            $dbRirAsn->getRir() !== $fileRirAsn->getRir() or
                            $dbRirAsn->getCountry() !== $fileRirAsn->getCountry() or
                            $dbRirAsn->getStatus() !== $fileRirAsn->getStatus() or
                            $dbRirAsn->getAllocatedAt()->format('Y-m-d') !== $fileRirAsn->getAllocatedAt()->format('Y-m-d') or
                            $dbRirAsn->getAsnStart() !== $fileRirAsn->getAsnStart() or
                            $dbRirAsn->getAsnEnd() !== $fileRirAsn->getAsnEnd() or
                            $dbRirAsn->getAsnCount() !== $fileRirAsn->getAsnCount()
                        ) {

                            $this->whoisService->updateRirAsnObject($fileRirAsn, $dbRirAsn);

                            ##
                            $this->em->persist($dbRirAsn);
                        }

                        ##
                        $this->em->detach($fileRirAsn);
                    }

                    ## Progress Bar
                    $progressBar->advance();
                }

                ## Flush every 1000 loop.
                if (($countLoop % 1000) == 0)
                    $this->em->flush();
                $countLoop++;
            }

            ## Progress Bar.
            $progressBar->finish();

            ## Last flush.
            $this->em->flush();

            ## Text.
            $io->text('.');







            ### Comparison: Database => File ###
            ### Delete outdated data from database ###

            ## Text
            $io->text('.');
            $io->comment('Deleting outdated data...');

            ## Progress bar.
            $progressBar = new ProgressBar($output, 3);
            $progressBar->start();

            ## IPv4.
            $rirIpNetworks = $this->rirIpNetworkRepository->findBy(['rir'=>$rir, 'ipVersion'=>4]);
            foreach ($rirIpNetworks as $rirIpNetwork)
                if (empty($fileSortedObjectsByTypes['ipv4'][$rirIpNetwork->getHandle()]))
                    $this->em->remove($rirIpNetwork);
            $this->em->flush();
            ## Progress Bar.
            $progressBar->advance();

            ## IPv6.
            $rirIpNetworks = $this->rirIpNetworkRepository->findBy(['rir'=>$rir, 'ipVersion'=>6]);
            foreach ($rirIpNetworks as $rirIpNetwork)
                if (empty($fileSortedObjectsByTypes['ipv6'][$rirIpNetwork->getHandle()]))
                    $this->em->remove($rirIpNetwork);
            $this->em->flush();
            ## Progress Bar.
            $progressBar->advance();

            ## ASNs.
            $rirAsns = $this->rirAsnRepository->findBy(['rir'=>$rir]);
            foreach ($rirAsns as $rirAsn)
                if (empty($fileSortedObjectsByTypes['asn'][$rirAsn->getHandle()]))
                    $this->em->remove($rirAsn);
            $this->em->flush();
            ## Progress Bar.
            $progressBar->advance();

            ## Progress Bar.
            $progressBar->finish();


//                    }
//                }
//            }



            ## Last flush.
            $this->em->flush();

            ## Text.
            $io->text('.');
        }

        ## Text.
        $io->info("Done.");

        ## Return.
        return 0;
    }
}
