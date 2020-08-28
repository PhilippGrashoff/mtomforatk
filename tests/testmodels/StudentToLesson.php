<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\MToMModel;

class StudentToLesson extends MToMModel
{
    public $table = 'student_to_lesson';

    protected $fieldNamesForReferencedClasses =
        [
            'student_id' => Student::class,
            'lesson_id' => Lesson::class
        ];

    /**
     * only used to add an additional field to test additionalFields parameter in tests
     */
    public function init(): void
    {
        parent::init();
        $this->addField('some_other_field');
    }
}
