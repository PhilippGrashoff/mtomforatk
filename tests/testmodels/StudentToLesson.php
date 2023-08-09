<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use mtomforatk\MToMModel;

class StudentToLesson extends MToMModel
{
    public $table = 'student_to_lesson';

    protected array $fieldNamesForReferencedClasses =
        [
            'student_id' => Student::class,
            'lesson_id' => Lesson::class
        ];

    /**
     * only used to add an additional field to test additionalFields parameter in tests
     */
    protected function init(): void
    {
        parent::init();
        $this->addField('some_other_field');
    }
}
