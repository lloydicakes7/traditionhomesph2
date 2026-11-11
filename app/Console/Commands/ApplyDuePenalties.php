<?php

namespace App\Console\Commands;

use App\Actions\Collections\MonthlyDues\ApplyDuePenalties as ApplyDuePenaltiesAction;
use Illuminate\Console\Command;

/**
 * The scheduled half of the Penalty Settings.
 *
 * The office configures what an overdue month is charged (Settings → Collections
 * → Penalty Settings); this runs daily and charges it, once, to every due that
 * has outlived its due date and still carries nothing. The rule itself lives in
 * the ApplyDuePenalties action own the rule, so
 * the page, this command and the report that reads a due all price a penalty
 * the same way.
 */
class ApplyDuePenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dues:apply-penalties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge the configured penalty to monthly dues past their due date';

    /** @var ApplyDuePenaltiesAction */
    private $dues;

    public function __construct(ApplyDuePenaltiesAction $dues)
    {
        parent::__construct();

        $this->dues = $dues;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $penalised = $this->dues->handle();

        $this->info('Penalised ' . $penalised . ' overdue monthly due invoice(s).');

        return 0;
    }
}
