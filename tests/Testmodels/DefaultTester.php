<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests\Testmodels;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use PhilippR\Atk4\MToM\MToMTait;


class DefaultTester extends Model
{
    use MToMTait;

    public $table = 'student';
    protected array $referenceDefaults = [];
    protected array $mToMModelDefaults = [];


    /**
     * @throws Exception
     */
    protected function init(): void
    {
        parent::init();

        $this->addField('name');

        $this->addMToMReferenceAndDeleteHook(
            StudentToLesson::class,
            '',
            $this->referenceDefaults,
            $this->mToMModelDefaults
        );
    }
}
