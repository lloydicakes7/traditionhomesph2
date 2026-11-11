<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveOpeningBalanceAndFundsAddedAccounts extends Migration
{
    public function up()
    {
        $accountIds = DB::table('accounts')
            ->whereIn('system_key', ['opening_balance', 'funds_added'])
            ->pluck('id');

        DB::table('journal_lines')
            ->whereIn('account_id', $accountIds)
            ->update(['account_name' => 'External funds']);

        DB::table('accounts')
            ->whereIn('id', $accountIds)
            ->delete();
    }

    public function down()
    {
    }
}
