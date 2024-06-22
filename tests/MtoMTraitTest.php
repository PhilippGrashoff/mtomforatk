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
class MtoMTraitTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new Sql('sqlite::memory:');
        $this->createMigrator(new Student($this->db))->create();
        $this->createMigrator(new StudentToLesson($this->db))->create();
        $this->createMigrator(new Lesson($this->db))->create();
    }

    public function testOnAfterDeleteHookDeletesMToMModel(): void
    {
        $student = (new Student($this->db))->createEntity();
        $lesson = (new Lesson($this->db))->createEntity();
        $student->save();
        $lesson->save();
        StudentToLesson::addMToMRelation($student, $lesson);
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