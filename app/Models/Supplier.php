<?php

namespace App\Models;

use App\Models\Expense\Expense;
use Illuminate\Database\Eloquent\Model;

/**
 * A trade or business the association buys from.
 *
 * A supplier only names who an expense was spent on. It is never disabled:
 * removing one is a delete, and an expense that already names it keeps the
 * record.
 */
class Supplier extends Model
{
    protected $fillable = ['name', 'description'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    public function scopeSearch($query, ?string $term)
    {
        return $term ? $query->where('name', 'like', "%{$term}%") : $query;
    }
}
