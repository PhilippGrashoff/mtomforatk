<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM\Tests;


use Atk4\Data\Exception;
use Atk4\Data\Model;
use Atk4\Data\Persistence\Sql;
use Atk4\Data\Schema\TestCase;
use PhilippR\Atk4\MToM\MToMTait;
use PhilippR\Atk4\MToM\Tests\Testmodels\DefaultTester;
use PhilippR\Atk4\MToM\Tests\Testmodels\Lesson;
use PhilippR\Atk4\MToM\Tests\Testmodels\Student;
use PhilippR\Atk4\MToM\Tests\Testmodels\StudentToLesson;

/**
 * Class MToMTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class ModelWithMtoMTraitTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new Student($this->db))->create();
        $this->createMigrator(new StudentToLesson($this->db))->create();
        $this->createMigrator(new Lesson($this->db))->create();
    }

    public function testMToMAdding(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($this->db))->action('count')->getOne());

        //adding again shouldn't create a new record
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($this->db))->action('count')->getOne());
    }

    public function testMToMAddingThrowExceptionThisNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->save();

        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testMToMAddingThrowExceptionEntityNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testMToMAddingById(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson->getId());
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($this->db))->action('count')->getOne());
    }

    public function testMToMAddingByInvalidId(): void
    {
        $student = (new Student($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($this->db), 123456);
    }

    public function testMToMRemoval(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertEquals($studentToLessonCount + 1, (new StudentToLesson($this->db))->action('count')->getOne());
        $student->removeMToMRelation(new StudentToLesson($this->db), $lesson);
        //should be removed
        self::assertEquals($studentToLessonCount, (new StudentToLesson($this->db))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testMToMRemovalThrowExceptionThisNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->save();

        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testMToMRemovalThrowExceptionEntityNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testHasMToMReference(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertTrue($student->hasMToMRelation(new StudentToLesson($this->db), $lesson));
        self::assertTrue($lesson->hasMToMRelation(new StudentToLesson($this->db), $student));

        $student->removeMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertFalse($student->hasMToMRelation(new StudentToLesson($this->db), $lesson));
        self::assertFalse($lesson->hasMToMRelation(new StudentToLesson($this->db), $student));
    }

    public function testhasMToMRelationThrowExceptionThisNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $lesson->save();

        self::expectException(Exception::class);
        $student->hasMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testhasMToMRelationThrowExceptionEntityNotLoaded(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();

        self::expectException(Exception::class);
        $student->hasMToMRelation(new StudentToLesson($this->db), $lesson);
    }

    public function testMToMAddingWrongClassException(): void
    {
        $student = (new Student($this->db))->createEntity();
        $student->save();
        self::expectException(Exception::class);
        $student->addMToMRelation(new StudentToLesson($this->db), $student);
    }

    public function testMToMRemovalWrongClassException(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::expectException(Exception::class);
        $student->removeMToMRelation(new StudentToLesson($this->db), $student);
    }

    public function testhasMToMRelationWrongClassException(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::expectException(Exception::class);
        $student->hasMToMRelation(new StudentToLesson($this->db), $student);
    }

    public function testAddAdditionalFields(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();

        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        $student->addMToMRelation(new StudentToLesson($this->db), $lesson, ['some_other_field' => 'LALA']);
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

        $res = $student->addMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertInstanceOf(StudentToLesson::class, $res);
        $res = $student->removeMToMRelation(new StudentToLesson($this->db), $lesson);
        self::assertInstanceOf(StudentToLesson::class, $res);
    }

    public function testOnAfterDeleteHookDeletesMToMModel(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        $student->addLesson($lesson);
        //new StudentToModel record created
        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        self::assertEquals(1, $studentToLessonCount);
        $student->delete();
        $studentToLessonCount = (new StudentToLesson($this->db))->action('count')->getOne();
        self::assertEquals(0, $studentToLessonCount);
    }

    public function testReferenceNameDefaultsToClassName(): void
    {
        $student = (new Student($this->db))->createEntity();
        self::assertTrue($student->hasReference(StudentToLesson::class));
    }

    public function testAddMToMModelDefaults(): void
    {
        $defaultTester = new DefaultTester(
            $this->db,
            ['mToMModelDefaults' => ['caption' => 'SomeOtherCaption']]
        );
        self::assertSame(
            'SomeOtherCaption',
            $defaultTester->ref(StudentToLesson::class)->getModelCaption()
        );
    }

    public function testAddReferenceDefaults(): void
    {
        $defaultTester = new DefaultTester(
            $this->db,
            ['referenceDefaults' => ['caption' => 'SomeOtherCaption']]
        );
        self::assertSame(
            'SomeOtherCaption',
            $defaultTester->getReference(StudentToLesson::class)->caption
        );
    }

    public function testExceptionInvalidClassNamePassedToReferenceCreation(): void
    {
        $class = new class() extends Model {

            use MToMTait;

            public $table = 'some_table';

            protected function init(): void
            {
                parent::init();

                $this->addMToMReferenceAndDeleteHook('SomeNonExistantClassName');
            }
        };
        self::expectException(Exception::class);
        $model = new $class($this->db);
    }

    public function testDifferentReferenceNameCanBeGiven(): void
    {
        $class = new class() extends Model {

            use MToMTait;

            public $table = 'some_table';

            protected function init(): void
            {
                parent::init();

                $this->addMToMReferenceAndDeleteHook(StudentToLesson::class, 'SomeOtherReferenceName');
            }
        };

        $model = new $class($this->db);

        self::assertFalse($model->hasReference(StudentToLesson::class));
        self::assertTrue($model->hasReference('SomeOtherReferenceName'));
    }
}