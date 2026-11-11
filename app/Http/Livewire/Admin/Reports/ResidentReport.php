<?php

namespace App\Http\Livewire\Admin\Reports;

use App\Http\Livewire\AdminComponent;
use App\Queries\Reports\ResidentReportQuery;

/**
 * The Residents Report — the association read as a population: how old it is,
 * what its households look like and where its people came from — with the mix
 * of units it owns beside them (see ResidentReportQuery, which counts the
 * property mix over `properties` rather than over the residents' households).
 *
 * Its charts are laid out three to a row on a wide screen and one per row below
 * it: the page holds nine of them, and a grid that only ever fills the row it is
 * on is what keeps a card from standing beside an empty space.
 *
 * Like the Community Reports page it carries no export: it is several
 * distributions of the same records rather than one table a CSV could hold, and
 * every figure in it is reachable from the Homeowners module that owns the
 * record. What it adds is the shape — a pie where a split is the question, a
 * line where growth is, a box where spread is.
 */
class ResidentReport extends AdminComponent
{
    /** @var ResidentReportQuery */
    protected $residents;

    public function boot(ResidentReportQuery $residents)
    {
        $this->residents = $residents;
    }

    public function render()
    {
        $this->authorize('report.viewAny');

        return view('livewire.admin.reports.residents', [
            'report' => $this->residents->summary(),
        ]);
    }
}
