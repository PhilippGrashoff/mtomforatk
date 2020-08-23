<?php declare(strict_types=1);

namespace mtomforatk\tests\testmodels;

use atk4\data\Model;
use mtomforatk\MToMModel;

class StudentToLesson extends MToMModel
{
    public $table = 'student_to_lesson';

    public $fieldName1 = 'student_id';
    public $className1 = Student::class;
    public $fieldName2 = 'lesson_id';
    public $className2 = Lesson::class;
}
