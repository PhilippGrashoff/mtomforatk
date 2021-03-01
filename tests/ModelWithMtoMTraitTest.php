<?php declare(strict_types=1);

namespace mtomforatk\tests;


use atk4\data\Model;
use mtomforatk\ModelWithMToMTrait;
use mtomforatk\tests\testmodels\DefaultTester;
use mtomforatk\tests\testmodels\Lesson;
use mtomforatk\tests\testmodels\Student;
use mtomforatk\tests\testmodels\StudentToLesson;
use traitsforatkdata\TestCase;
use atk4\data\Exception;

/**
 * Class MToMTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class ModelWithMtoMTraitTest extends TestCase
{   

    protected $sqlitePersistenceModels = [
        StudentToLesson::class,
        Student::class,
        Lesson::class
    ];

    public function testMToMAdding()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($persistence))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        //adding again shouldnt create a new record
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }

    public function testMToMAddingThrowExceptionThisNotLoaded()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testMToMAddingThrowExceptionObjectNotLoaded()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testMToMAddingById()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($persistence))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($persistence),$lesson->get('id'));
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }

    public function testMToMAddingByInvalidId()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $student->save();

        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($persistence), 123456);
    }

    public function testMToMRemoval()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($persistence))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($persistence))->action('count')->getOne());
        $student->removeMToMRelation(new StudentToLesson($persistence), $lesson);
        //should be removed
        self::assertEquals($studentToLessonCount, (new StudentToLesson($persistence))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testMToMRemovalThrowExceptionThisNotLoaded()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testMToMRemovalThrowExceptionObjectNotLoaded()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testHasMToMReference()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertTrue($student->hasMToMRelation(new StudentToLesson($persistence), $lesson));
        self::assertTrue($lesson->hasMToMRelation(new StudentToLesson($persistence), $student));

        $student->removeMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertFalse($student->hasMToMRelation(new StudentToLesson($persistence), $lesson));
        self::assertFalse($lesson->hasMToMRelation(new StudentToLesson($persistence), $student));
    }

    public function testhasMToMRelationThrowExceptionThisNotLoaded()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $student->hasMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testhasMToMRelationThrowExceptionObjectNotLoaded()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $student->hasMToMRelation(new StudentToLesson($persistence), $lesson);
    }

    public function testMToMAddingWrongClassException()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $student->save();
        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($persistence), $student);
    }

    public function testMToMRemovalWrongClassException()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($persistence), $student);
    }

    public function testhasMToMRelationWrongClassException()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::expectException(Exception::class);
        $student->hasMToMRelation(new StudentToLesson($persistence), $student);
    }

    public function testAddAdditionalFields()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($persistence))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($persistence), $lesson, ['some_other_field' => 'LALA']);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        $mtommodel = new StudentToLesson($persistence);
        $mtommodel->loadAny();
        self::assertEquals($mtommodel->get('some_other_field'), 'LALA');
    }

    public function testMToMModelIsReturned()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $res = $student->addMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertInstanceOf(StudentToLesson::class, $res);
        $res = $student->removeMToMRelation(new StudentToLesson($persistence), $lesson);
        self::assertInstanceOf(StudentToLesson::class, $res);
    }

    public function testOnAfterDeleteHookDeletesMToMModel()
    {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $student->addLesson($lesson);
        //new StudentToModel record created
        $studentToLessonCount = (new StudentToLesson($persistence))->action('count')->getOne();
        self::assertEquals(1, $studentToLessonCount);
        $student->delete();
        $studentToLessonCount = (new StudentToLesson($persistence))->action('count')->getOne();
        self::assertEquals(0, $studentToLessonCount);
    }

    public function testReferenceNameDefaultsToClassName() {
        $persistence = $this->getSqliteTestPersistence();
        $student = new Student($persistence);
        self::assertTrue($student->hasRef(StudentToLesson::class));
    }

    public function testAddMToMModelDefaults(): void {
        $defaultTester = new DefaultTester($this->getSqliteTestPersistence(), ['mToMModelDefaults' => ['caption' => 'SomeOtherCaption']]);
        self::assertSame(
            'SomeOtherCaption',
            $defaultTester->ref(StudentToLesson::class)->getModelCaption()
        );
    }

    public function testAddReferenceDefaults(): void {
        $defaultTester = new DefaultTester($this->getSqliteTestPersistence(), ['referenceDefaults' => ['caption' => 'SomeOtherCaption']]);
        self::assertSame(
            'SomeOtherCaption',
            $defaultTester->getRef(StudentToLesson::class)->caption
        );
    }

    public function testExceptionInvalidClassNamePassedToReferenceCreation() {
        $persistence = $this->getSqliteTestPersistence();
        $class = new class() extends Model {

            use ModelWithMToMTrait;

            public $table = 'some_table';

            protected function init(): void
            {
                parent::init();

                $this->addMToMReferenceAndDeleteHook('SomeNonExistantClassName');
            }
        };
        self::expectException(Exception::class);
        $model = new $class($persistence);
    }

    public function testDifferentReferenceNameCanBeGiven() {
        $persistence = $this->getSqliteTestPersistence();
        $class = new class() extends Model {

            use ModelWithMToMTrait;

            public $table = 'some_table';

            protected function init(): void
            {
                parent::init();

                $this->addMToMReferenceAndDeleteHook(StudentToLesson::class, 'SomeOtherReferenceName');
            }
        };

        $model = new $class($persistence);

        self::assertFalse($model->hasRef(StudentToLesson::class));
        self::assertTrue($model->hasRef('SomeOtherReferenceName'));
    }
}