<?php declare(strict_types=1);

namespace mtomforatk\tests;

use atk4\data\Exception;
use traitsforatkdata\TestCase;
use mtomforatk\MToMModel;
use mtomforatk\tests\testmodels\Lesson;
use mtomforatk\tests\testmodels\StudentToLesson;
use mtomforatk\tests\testmodels\Student;
use atk4\data\Model;


class MToMModelTest extends TestCase
{
    protected $sqlitePersistenceModels = [
        Student::class,
        StudentToLesson::class,
        Lesson::class
    ];

    public function testInit()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::assertTrue($studentToLesson->hasField('student_id'));
        self::assertTrue($studentToLesson->hasField('lesson_id'));
        self::assertTrue($studentToLesson->hasRef('student_id'));
        self::assertTrue($studentToLesson->hasRef('lesson_id'));
    }

    public function testExceptionMoreThanTwoElementsInFieldNamesForReferencedClasses()
    {
        $persistence = $this->getSqliteTestPersistence();
        $someClassWith3Elements = new class() extends MToMModel {
            protected $fieldNamesForReferencedClasses = [
                'field1' => 'Blabla',
                'field2' => 'DaDa',
                'field3' => 'Gaga'
            ];
        };
        self::expectException(Exception::class);
        new $someClassWith3Elements($persistence);
    }

    public function testExceptionLessThanTwoElementsInFieldNamesForReferencedClasses()
    {
        $persistence = $this->getSqliteTestPersistence();
        $someClassWith1Element = new class() extends MToMModel {
            protected $fieldNamesForReferencedClasses = [
                'field1' => 'Blabla'
            ];
        };
        self::expectException(Exception::class);
        $instance = new $someClassWith1Element($persistence);
    }

    public function testExceptionInvalidClassInFieldNamesForReferencedClasses()
    {
        $persistence = $this->getSqliteTestPersistence();
        $someClassWithInvalidClassDefinition = new class() extends MToMModel {
            protected $fieldNamesForReferencedClasses = [
                'field1' => Student::class,
                'field2' => 'SomeNonExistantModel'
            ];
        };
        self::expectException(Exception::class);
        $instance = new $someClassWithInvalidClassDefinition($persistence);
    }

    public function testReferenceObjectKeysCreatedInArray()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        $referenceObjects = (new \ReflectionClass($studentToLesson))->getProperty('referenceObjects');
        $referenceObjects->setAccessible(true);
        $value = $referenceObjects->getValue($studentToLesson);
        self::assertIsArray($value);
        self::assertArrayHasKey(Student::class, $value);
        self::assertArrayHasKey(Lesson::class, $value);
    }

    public function testAddLoadedObject()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $student->save();
        $studentToLesson = new StudentToLesson($persistence);
        $studentToLesson->addLoadedObject($student);
        $props = (new \ReflectionClass($studentToLesson))->getProperty(
            'referenceObjects'
        );//getProperties(\ReflectionProperty::IS_PROTECTED);
        $props->setAccessible(true);
        $value = $props->getValue($studentToLesson);
        self::assertSame(
            $student,
            $value[Student::class],
        );
    }

    public function testAddLoadedObjectExceptionWrongClassPassed()
    {
        $persistence = $this->getSqliteTestPersistence();
        $otherClass = new class() extends Model {
            public $table = 'sometable';
        };
        $model = new $otherClass($persistence);
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $studentToLesson->addLoadedObject($model);
    }

    public function testgetObject()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $studentToLesson = new StudentToLesson($persistence);

        //gets loaded from DB
        $studentToLesson->set('student_id', $student->get('id'));
        $resA = $studentToLesson->getObject(Student::class);
        //different Object but same ID
        self::assertNotSame($student, $resA);
        self::assertSame($student->get('id'), $resA->get('id'));

        //is put in referenceObjects Array, should return same object
        $studentToLesson->addLoadedObject($lesson);
        $resB = $studentToLesson->getObject(Lesson::class);
        self::assertSame($lesson, $resB);
    }

    public function testgetObjectExceptionInvalidClass()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $resA = $studentToLesson->getObject('SomeNonSetClass');
    }

    public function testgetFieldNameForModel()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::assertSame('student_id', $studentToLesson->getFieldNameForModel(new Student($persistence)));
        self::assertSame('lesson_id', $studentToLesson->getFieldNameForModel(new Lesson($persistence)));
    }

    public function testgetFieldNameForModelExceptionWrongClass()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $studentToLesson->getFieldNameForModel(new StudentToLesson($persistence));
    }

    public function testGetOtherModelClass()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::assertSame(Lesson::class, $studentToLesson->getOtherModelClass(new Student($persistence)));
        self::assertSame(Student::class, $studentToLesson->getOtherModelClass(new Lesson($persistence)));
    }

    public function testGetOtherModelClassExceptionWrongClass()
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $studentToLesson->getOtherModelClass(new StudentToLesson($persistence));
    }

    public function testAddConditionForModel()
    {
        $persistence = $this->getSqliteTestPersistence();
        $lesson = new Lesson($persistence);
        $lesson->set('id', 234);
        $lesson->save();
        $student = new Student($persistence);
        $student->set('id', 456);
        $student->save();

        $student->addMToMRelation((new StudentToLesson($persistence)), $lesson);

        $studentToLesson = new StudentToLesson($persistence);
        $studentToLesson->addConditionForModel($lesson);
        $studentToLesson->addConditionForModel($student);

        $studentToLesson->loadAny();
        self::assertEquals(
            234,
            $studentToLesson->get('lesson_id')
        );
        self::assertEquals(
            456,
            $studentToLesson->get('student_id')
        );

    }
}
