<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\MToM\JunctionModel;
use PhilippR\Atk4\MToM\Tests\Testmodels\Lesson;
use PhilippR\Atk4\MToM\Tests\Testmodels\Student;
use PhilippR\Atk4\MToM\Tests\Testmodels\StudentToLesson;
use PhilippR\Atk4\MToM\Tests\Testmodels\Teacher;
use ReflectionClass;


class JunctionModelTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new Student($this->db))->create();
        $this->createMigrator(new StudentToLesson($this->db))->create();
        $this->createMigrator(new Lesson($this->db))->create();
        $this->createMigrator(new Teacher($this->db))->create();
    }

    public function testInit(): void
    {
        $studentToLesson = new StudentToLesson($this->db);
        self::assertTrue($studentToLesson->hasField('student_id'));
        self::assertTrue($studentToLesson->hasField('lesson_id'));
        self::assertTrue($studentToLesson->hasReference('student_id'));
        self::assertTrue($studentToLesson->hasReference('lesson_id'));
    }

    public function testExceptionMoreThanTwoElementsInFieldNamesForReferencedClasses(): void
    {
        $someClassWith3Elements = new class() extends JunctionModel {
            protected static array $relationFieldNames = [
                'field1' => 'Blabla',
                'field2' => 'DaDa',
                'field3' => 'Gaga'
            ];
        };
        //TODO: assertExceptionmessage
        self::expectException(Exception::class);
        new $someClassWith3Elements($this->db);
    }

    public function testExceptionLessThanTwoElementsInFieldNamesForReferencedClasses(): void
    {
        $someClassWith1Element = new class() extends JunctionModel {
            protected static array $relationFieldNames = [
                'field1' => 'Blabla'
            ];
        };
        //TODO: assertExceptionmessage
        self::expectException(Exception::class);
        $instance = new $someClassWith1Element($this->db);
    }

    public function testExceptionInvalidClassInFieldNamesForReferencedClasses(): void
    {
        $someClassWithInvalidClassDefinition = new class() extends JunctionModel {
            protected static array $relationFieldNames = [
                'field1' => Student::class,
                'field2' => 'SomeNonExistantModel'
            ];
        };
        //TODO: assertExceptionmessage
        self::expectException(Exception::class);
        $instance = new $someClassWithInvalidClassDefinition($this->db);
    }

    public function testReferencedEntitiesKeysCreatedInArray(): void
    {
        $studentToLesson = new StudentToLesson($this->db);
        $referencedEntities = (new ReflectionClass($studentToLesson))->getProperty('referencedEntities');
        $referencedEntities->setAccessible(true);
        $value = $referencedEntities->getValue($studentToLesson);
        self::assertIsArray($value);
        self::assertArrayHasKey(Student::class, $value);
        self::assertArrayHasKey(Lesson::class, $value);
    }

    public function testAddLoadedEntity(): void
    {
        $student = (new Student($this->db))->createEntity();
        $student->save();
        $studentToLesson = new StudentToLesson($this->db);
        $studentToLesson->addReferencedEntity($student);
        $props = (new ReflectionClass($studentToLesson))->getProperty('referencedEntities');
        //getProperties(\ReflectionProperty::IS_PROTECTED);
        $props->setAccessible(true);
        $value = $props->getValue($studentToLesson);
        self::assertSame(
            $student,
            $value[Student::class],
        );
    }

    public function testAddReferencedEntityExceptionWrongClassPassed(): void
    {
        $otherClass = new class() extends Model {
            public $table = 'sometable';
        };
        $model = (new $otherClass($this->db))->createEntity();
        $studentToLesson = new StudentToLesson($this->db);
        self::expectException(Exception::class);
        $studentToLesson->addReferencedEntity($model);
    }

    public function testGetReferenceEntity(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        $studentToLesson = (new StudentToLesson($this->db))->createEntity();

        //gets loaded from DB
        $studentToLesson->set('student_id', $student->getId());
        $resA = $studentToLesson->getReferencedEntity(Student::class);
        //different Object but same ID
        self::assertNotSame($student, $resA);
        self::assertSame($student->getId(), $resA->getId());

        //is put in $referencedEntities Array, should return same object
        $studentToLesson->addReferencedEntity($lesson);
        $resB = $studentToLesson->getReferencedEntity(Lesson::class);
        self::assertSame($lesson, $resB);
    }

    public function testGetReferencedEntityExceptionInvalidClass(): void
    {
        $studentToLesson = (new StudentToLesson($this->db))->createEntity();
        self::expectException(Exception::class);
        $studentToLesson->getReferencedEntity('SomeNonSetClass');
    }

    public function testGetFieldNameForModel(): void
    {
        $studentToLesson = new StudentToLesson($this->db);
        self::assertSame('student_id', $studentToLesson->getFieldNameForModel(new Student($this->db)));
        self::assertSame('lesson_id', $studentToLesson->getFieldNameForModel(new Lesson($this->db)));
    }

    public function testGetFieldNameForModelExceptionWrongClass(): void
    {
        $studentToLesson = new StudentToLesson($this->db);
        self::expectException(Exception::class);
        $studentToLesson->getFieldNameForModel(new StudentToLesson($this->db));
    }

    public function testGetOtherModelClass(): void
    {
        $studentToLesson = new StudentToLesson($this->db);
        self::assertSame(Lesson::class, $studentToLesson->getOtherModelClass(new Student($this->db)));
        self::assertSame(Student::class, $studentToLesson->getOtherModelClass(new Lesson($this->db)));
    }

    public function testGetOtherModelClassExceptionWrongClass(): void
    {
        $studentToLesson = new StudentToLesson($this->db);
        self::expectException(Exception::class);
        $studentToLesson->getOtherModelClass(new StudentToLesson($this->db));
    }

    public function testGetReferencedEntityExceptionInvalidArrayKeyGiven(): void
    {
        $studentToLesson = (new StudentToLesson($this->db))->createEntity();
        $studentToLesson->set('student_id', 1);
        $studentToLesson->set('lesson_id', 1);
        $studentToLesson->save();
        self::expectExceptionMessage('Invalid className passed in getReferencedEntity');
        $studentToLesson->getReferencedEntity('someWrongClassName');
    }

    public function testSavingWithoutIDsOfEntitiesSetFails(): void
    {
        $studentToLesson = (new StudentToLesson($this->db))->createEntity();
        self::expectExceptionMessage('Must not be empty');
        $studentToLesson->save();
    }

    public function testAddReferencedEntityExceptionInvalidModelClassGiven(): void
    {
        $studentToLesson = (new StudentToLesson($this->db))->createEntity();
        $studentToLesson->set('student_id', 1);
        $studentToLesson->set('lesson_id', 1);
        $studentToLesson->save();
        $teacher = (new Teacher($this->db))->createEntity();
        $teacher->save();
        self::expectExceptionMessage(
            'This PhilippR\Atk4\MToM\JunctionModel does not have a reference to PhilippR\Atk4\MToM\Tests\Testmodels\Teacher'
        );
        $studentToLesson->addReferencedEntity($teacher);
    }

    public function testAddConditionForModel(): void
    {
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->set('id', 234);
        $lesson->save();
        $student = (new Student($this->db))->createEntity();
        $student->set('id', 456);
        $student->save();

        StudentToLesson::addMToMRelation($student, $lesson);

        $studentToLesson = new StudentToLesson($this->db);
        $addConditionHelper = \Closure::bind(
            static function (Model $entity) use ($studentToLesson) {
                $studentToLesson->addConditionForModel($entity);
            },
            null,
            $studentToLesson
        );
        $addConditionHelper($lesson);
        $addConditionHelper($student);
        $studentToLesson = $studentToLesson->loadAny();
        self::assertSame(
            234,
            $studentToLesson->get('lesson_id')
        );
        self::assertSame(
            456,
            $studentToLesson->get('student_id')
        );
    }

    public function testMToMAdding(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        StudentToLesson::addMToMRelation($student, $lesson);
        self::assertEquals(
            $studentToLessonCount + 1,
            (new StudentToLesson($this->db))->action('count')->getOne()
        );

        //adding again shouldn't create a new record
        StudentToLesson::addMToMRelation($student, $lesson);
        self::assertEquals(
            $studentToLessonCount + 1,
            (new StudentToLesson($this->db))->action('count')->getOne()
        );

        //changing order also doesn't create a new record
        StudentToLesson::addMToMRelation($lesson, $student);
        self::assertEquals(
            $studentToLessonCount + 1,
            (new StudentToLesson($this->db))->action('count')->getOne()
        );
    }

    public function testMToMAddingThrowExceptionThisNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->save();

        self::expectException(Exception::class);
        StudentToLesson::addMToMRelation($student, $lesson);
    }

    public function testMToMAddingThrowExceptionEntityNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        StudentToLesson::addMToMRelation($student, $lesson);
    }

    public function testMToMAddingById(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        StudentToLesson::addMToMRelation($student, $lesson->getId());
        self::assertEquals(
            $studentToLessonCount + 1,
            (new StudentToLesson($this->db))->action('count')->getOne()
        );
    }

    public function testMToMAddingByInvalidId(): void
    {
        $student = (new Student($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        StudentToLesson::addMToMRelation($student, 123456);
    }

    public function testMToMRemoval(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        StudentToLesson::addMToMRelation($student, $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($this->db))->action('count')->getOne());
        StudentToLesson::removeMToMRelation($student, $lesson);
        //should be removed
        self::assertEquals($studentToLessonCount, (new StudentToLesson($this->db))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        self::expectException(Exception::class);
        StudentToLesson::removeMToMRelation($student, $lesson);
    }

    public function testMToMRemovalThrowExceptionThisNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->save();

        self::expectException(Exception::class);
        StudentToLesson::removeMToMRelation($student, $lesson);
    }

    public function testMToMRemovalThrowExceptionEntityNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        StudentToLesson::removeMToMRelation($student, $lesson);
    }

    public function testHasMToMReference(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        StudentToLesson::addMToMRelation($student, $lesson);
        self::assertTrue(StudentToLesson::hasMToMRelation($student, $lesson));
        self::assertTrue(StudentToLesson::hasMToMRelation($lesson, $student));

        StudentToLesson::removeMToMRelation($student, $lesson);
        self::assertFalse(StudentToLesson::hasMToMRelation($student, $lesson));
        self::assertFalse(StudentToLesson::hasMToMRelation($lesson, $student));
    }

    public function testhasMToMRelationThrowExceptionThisNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->save();

        self::expectException(Exception::class);
        StudentToLesson::hasMToMRelation($student, $lesson);
    }

    public function testhasMToMRelationThrowExceptionEntityNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        StudentToLesson::hasMToMRelation($student, $lesson);
    }

    public function testMToMAddingWrongClassException(): void
    {
        $student = (new Student($this->db))->createEntity();
        $student->save();
        self::expectException(Exception::class);
        StudentToLesson::addMToMRelation($student, $student);
    }

    public function testMToMRemovalWrongClassException(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        StudentToLesson::addMToMRelation($student, $lesson);
        self::expectException(Exception::class);
        StudentToLesson::removeMToMRelation($student, $student);
    }

    public function testhasMToMRelationWrongClassException(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        StudentToLesson::addMToMRelation($student, $lesson);
        self::expectException(Exception::class);
        StudentToLesson::hasMToMRelation($student, $student);
    }

    public function testAddAdditionalFields(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        StudentToLesson::addMToMRelation($student, $lesson, ['some_other_field' => 'LALA']);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($this->db))->action('count')->getOne());

        $mtommodel = new StudentToLesson($this->db);
        $mtommodel = $mtommodel->loadAny();
        self::assertSame('LALA', $mtommodel->get('some_other_field'));
    }

    public function testMToMModelIsReturned(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $res = StudentToLesson::addMToMRelation($student, $lesson);
        self::assertInstanceOf(StudentToLesson::class, $res);
        $res = StudentToLesson::removeMToMRelation($student, $lesson);
        self::assertInstanceOf(StudentToLesson::class, $res);
    }
}
