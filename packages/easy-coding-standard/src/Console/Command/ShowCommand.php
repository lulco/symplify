<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyCodingStandard\Console\Reporter\CheckerListReporter;
use Symplify\EasyCodingStandard\Console\Style\EasyCodingStandardStyle;
use Symplify\EasyCodingStandard\FixerRunner\Application\FixerFileProcessor;
use Symplify\EasyCodingStandard\Guard\LoadedCheckersGuard;
use Symplify\EasyCodingStandard\SniffRunner\Application\SniffFileProcessor;
use Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;

final class ShowCommand extends AbstractSymplifyCommand
{
    public function __construct(
        private SniffFileProcessor $sniffFileProcessor,
        private FixerFileProcessor $fixerFileProcessor,
        private EasyCodingStandardStyle $easyCodingStandardStyle,
        private CheckerListReporter $checkerListReporter,
        private LoadedCheckersGuard $loadedCheckersGuard
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Show loaded checkers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->loadedCheckersGuard->areSomeCheckersRegistered()) {
            $this->loadedCheckersGuard->report();
            return self::FAILURE;
        }

        $totalCheckerCount = count($this->sniffFileProcessor->getCheckers())
            + count($this->fixerFileProcessor->getCheckers());

        $this->checkerListReporter->report($this->sniffFileProcessor->getCheckers(), 'PHP_CodeSniffer');
        $this->checkerListReporter->report($this->fixerFileProcessor->getCheckers(), 'PHP-CS-Fixer');

        $successMessage = sprintf(
            'Loaded %d checker%s in total',
            $totalCheckerCount,
            $totalCheckerCount === 1 ? '' : 's'
        );
        $this->easyCodingStandardStyle->success($successMessage);

        return self::SUCCESS;
    }
}
