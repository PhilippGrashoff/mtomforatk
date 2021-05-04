<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use Atk4\Data\Model;
use mtomforatk\ModelWithMToMTrait;


class DefaultTester extends Model
{
    use ModelWithMToMTrait;

    public $table = 'student';

    protected $referenceDefaults = [];
    protected $mToMModelDefaults = [];


    protected function init(): void {
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
