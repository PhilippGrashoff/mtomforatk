<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels\Persistence;

use atk4\data\Exception;
use atk4\data\Model;

/**
 * Temporary for making tests work with loadAny().
 * Remove when these are merged/resolved:
 * https://github.com/atk4/data/pull/696
 * https://github.com/atk4/data/pull/690
 */
class Array_ extends \atk4\data\Persistence\Array_ {

    /**
     * Tries to load first available record and return data record.
     */
    public function loadAny(Model $model, string $table = null): ?array
    {
        $row = $this->tryLoadAny($model, $table);
        if (!$row) {
            throw new Exception('No matching records were found!', 404);
        }

        return $row;
    }
}
