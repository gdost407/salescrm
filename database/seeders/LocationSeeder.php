<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cities')->delete();
        DB::table('states')->delete();
        DB::table('countries')->delete();

        $this->insertSqlFile('countries', 'countries.sql', ['id', 'shortname', 'name', 'phonecode']);
        $this->insertSqlFile('states', 'states.sql', ['id', 'name', 'country_id']);
        $this->insertSqlFile('cities', 'cities.sql', ['id', 'name', 'state_id']);
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function insertSqlFile(string $table, string $filename, array $columns): void
    {
        $path = database_path('seeders/sql/'.$filename);
        $sql = file_get_contents($path);

        if ($sql === false) {
            throw new RuntimeException("Unable to read location SQL file: {$filename}");
        }

        $rows = [];
        foreach ($this->parseRows($sql) as $values) {
            $rows[] = array_combine($columns, $values);

            if (count($rows) === 1000) {
                $this->insertRows($table, $rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            $this->insertRows($table, $rows);
        }
    }

    /**
     * @param  array<int, array<string, int|string|null>>  $rows
     */
    private function insertRows(string $table, array $rows): void
    {
        if ($table === 'cities') {
            $stateIds = DB::table('states')
                ->whereIn('id', array_column($rows, 'state_id'))
                ->pluck('id')
                ->all();
            $rows = array_values(array_filter($rows, fn (array $row): bool => in_array($row['state_id'], $stateIds, true)));
        }

        if ($rows !== []) {
            DB::table($table)->insert($rows);
        }
    }

    /**
     * @return \Generator<int, array<int, int|string|null>>
     */
    private function parseRows(string $sql): \Generator
    {
        preg_match_all('/INSERT\s+INTO[\s\S]*?VALUES/i', $sql, $matches, PREG_OFFSET_CAPTURE);

        if ($matches[0] === []) {
            throw new RuntimeException('No INSERT values found in location SQL file.');
        }

        foreach ($matches[0] as $matchIndex => $match) {
            $start = $match[1] + strlen($match[0]);
            $end = $matches[0][$matchIndex + 1][1] ?? strlen($sql);
            $length = $end - $start;
            $tuple = '';
            $insideTuple = false;
            $insideString = false;

            for ($index = 0; $index < $length; $index++) {
                $character = $sql[$start + $index];

                if ($character === "'") {
                    if ($insideString && ($index + 1 < $length) && $sql[$start + $index + 1] === "'") {
                        $tuple .= "''";
                        $index++;

                        continue;
                    }

                    $insideString = ! $insideString;
                }

                if (! $insideString && $character === '(' && ! $insideTuple) {
                    $insideTuple = true;
                    $tuple = '';

                    continue;
                }

                if (! $insideString && $character === ')' && $insideTuple) {
                    yield $this->parseValues($tuple);
                    $insideTuple = false;

                    continue;
                }

                if ($insideTuple) {
                    $tuple .= $character;
                }
            }
        }
    }

    /**
     * @return array<int, int|string|null>
     */
    private function parseValues(string $tuple): array
    {
        $values = [];
        $value = '';
        $insideString = false;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $character = $tuple[$index];

            if ($character === "'") {
                if ($insideString && ($index + 1 < $length) && $tuple[$index + 1] === "'") {
                    $value .= "'";
                    $index++;

                    continue;
                }

                $insideString = ! $insideString;

                continue;
            }

            if (! $insideString && $character === ',') {
                $values[] = $this->castValue($value);
                $value = '';

                continue;
            }

            $value .= $character;
        }

        $values[] = $this->castValue($value);

        return $values;
    }

    private function castValue(string $value): int|string|null
    {
        $value = trim($value);

        if (strtoupper($value) === 'NULL') {
            return null;
        }

        return is_numeric($value) ? (int) $value : $value;
    }
}
