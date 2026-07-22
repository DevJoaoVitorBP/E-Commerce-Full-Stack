<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueSlug implements ValidationRule
{
    private string $table;

    private ?int $ignoreId;

    private array $allowedTables = ['products', 'categories'];  // Whitelist de tabelas permitidas

    public function __construct(string $table, ?int $ignoreId = null)
    {
        if (! in_array($table, $this->allowedTables)) {
            throw new \InvalidArgumentException("Table '{$table}' is not allowed for UniqueSlug validation.");
        }
        $this->table = $table;
        $this->ignoreId = $ignoreId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)->where('slug', $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('O slug "'.$value.'" já existe. Deve ser único.');
        }
    }
}
