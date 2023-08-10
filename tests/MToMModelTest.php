<?php declare(strict_types=1);

namespace mtomforatk\tests;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use atkextendedtestcase\TestCase;
use mtomforatk\MToMModel;
use mtomforatk\tests\testmodels\Lesson;
use mtomforatk\tests\testmodels\Student;
use mtomforatk\tests\testmodels\StudentToLesson;


class MToMModelTest extends TestCase
{
    protected array $sqlitePersistenceModels = [
        Student::class,
        StudentToLesson::class,
        Lesson::class
    ];

    public function testInit(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::assertTrue($studentToLesson->hasField('student_id'));
        self::assertTrue($studentToLesson->hasField('lesson_id'));
        self::assertTrue($studentToLesson->hasReference('student_id'));
        self::assertTrue($studentToLesson->hasReference('lesson_id'));
    }

    public function testExceptionMoreThanTwoElementsInFieldNamesForReferencedClasses(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $someClassWith3Elements = new class() extends MToMModel {
            protected array $fieldNamesForReferencedClasses = [
                'field1' => 'Blabla',
                'field2' => 'DaDa',
                'field3' => 'Gaga'
            ];
        };
        self::expectException(Exception::class);
        new $someClassWith3Elements($persistence);
    }

    public function testExceptionLessThanTwoElementsInFieldNamesForReferencedClasses(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $someClassWith1Element = new class() extends MToMModel {
            protected array $fieldNamesForReferencedClasses = [
                'field1' => 'Blabla'
            ];
        };
        self::expectException(Exception::class);
        $instance = new $someClassWith1Element($persistence);
    }

    public function testExceptionInvalidClassInFieldNamesForReferencedClasses(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $someClassWithInvalidClassDefinition = new class() extends MToMModel {
            protected array $fieldNamesForReferencedClasses = [
                'field1' => Student::class,
                'field2' => 'SomeNonExistantModel'
            ];
        };
        self::expectException(Exception::class);
        $instance = new $someClassWithInvalidClassDefinition($persistence);
    }

    public function testReferenceObjectKeysCreatedInArray(): void
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

    public function testAddLoadedObject(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $student->save();
        $studentToLesson = new StudentToLesson($persistence);
        $studentToLesson->addReferenceEntity($student);
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

    public function testAddLoadedObjectExceptionWrongClassPassed(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $otherClass = new class() extends Model {
            public $table = 'sometable';
        };
        $model = new $otherClass($persistence);
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $studentToLesson->addReferenceEntity($model);
    }

    public function testgetObject(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $studentToLesson = new StudentToLesson($persistence);

        //gets loaded from DB
        $studentToLesson->set('student_id', $student->getId());
        $resA = $studentToLesson->getReferenceEntity(Student::class);
        //different Object but same ID
        self::assertNotSame($student, $resA);
        self::assertSame($student->getId(), $resA->getId());

        //is put in referenceObjects Array, should return same object
        $studentToLesson->addReferenceEntity($lesson);
        $resB = $studentToLesson->getReferenceEntity(Lesson::class);
        self::assertSame($lesson, $resB);
    }

    public function testgetObjectExceptionInvalidClass(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $resA = $studentToLesson->getReferenceEntity('SomeNonSetClass');
    }

    public function testgetFieldNameForModel(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::assertSame('student_id', $studentToLesson->getFieldNameForModel(new Student($persistence)));
        self::assertSame('lesson_id', $studentToLesson->getFieldNameForModel(new Lesson($persistence)));
    }

    public function testgetFieldNameForModelExceptionWrongClass(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $studentToLesson->getFieldNameForModel(new StudentToLesson($persistence));
    }

    public function testGetOtherModelClass(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::assertSame(Lesson::class, $studentToLesson->getOtherModelClass(new Student($persistence)));
        self::assertSame(Student::class, $studentToLesson->getOtherModelClass(new Lesson($persistence)));
    }

    public function testGetOtherModelClassExceptionWrongClass(): void
    {
        $persistence = $this->getSqliteTestPersistence();
        $studentToLesson = new StudentToLesson($persistence);
        self::expectException(Exception::class);
        $studentToLesson->getOtherModelClass(new StudentToLesson($persistence));
    }

    public function testAddConditionForModel(): void
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
