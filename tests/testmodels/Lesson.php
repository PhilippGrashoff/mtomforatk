<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\ModelWithMToMTrait;

class Lesson extends Model
{

    use ModelWithMToMTrait;

    public $table = 'lesson';


    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addField('name');

        $this->addMToMReference(StudentToLesson::class);
    }
}
