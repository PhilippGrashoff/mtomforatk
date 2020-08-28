<?php declare(strict_types=1);

namespace mtomforatk\tests;


use mtomforatk\tests\testmodels\Lesson;
use mtomforatk\tests\testmodels\Student;
use mtomforatk\tests\testmodels\StudentToLesson;
use atk4\core\AtkPhpunit\TestCase;
use mtomforatk\tests\testmodels\Persistence;
use atk4\data\Exception;

/**
 * Class MToMTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class ModelWithMtoMTraitTest extends TestCase
{

    public function testMToMAdding()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        //adding again shouldnt create a new record
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }

    public function testMToMAddingThrowExceptionThisNotLoaded()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testMToMAddingThrowExceptionObjectNotLoaded()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testMToMAddingById()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($student, 'addMToMRelation', $lesson->get('id'), new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }

    public function testMToMAddingByInvalidId()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', 11111, new StudentToLesson($persistence));
    }

    public function testMToMRemoval()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
        //should be removed
        self::assertEquals($mtom_count, (new StudentToLesson($persistence))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testMToMRemovalThrowExceptionThisNotLoaded()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testMToMRemovalThrowExceptionObjectNotLoaded()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testHasMToMReference()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));

        self::assertTrue($this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence)));
        self::assertTrue($this->callProtected($lesson, 'hasMToMRelation', $student, new StudentToLesson($persistence)));

        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertFalse(
            $this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence))
        );
        self::assertFalse(
            $this->callProtected($lesson, 'hasMToMRelation', $student, new StudentToLesson($persistence))
        );
    }

    public function testhasMToMRelationThrowExceptionThisNotLoaded()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testhasMToMRelationThrowExceptionObjectNotLoaded()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence));
    }

    public function testMToMAddingWrongClassException()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $student->save();
        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', $student, new StudentToLesson($persistence));
    }

    public function testMToMRemovalWrongClassException()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $student, new StudentToLesson($persistence));
    }

    public function testhasMToMRelationWrongClassException()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::expectException(Exception::class);
        $this->callProtected($student, 'hasMToMRelation', $student, new StudentToLesson($persistence));
    }

    public function testAddAdditionalFields()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected(
            $student,
            'addMToMRelation',
            $lesson,
            new StudentToLesson($persistence),
            ['some_other_field' => 'LALA']
        );
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        $mtommodel = new StudentToLesson($persistence);
        $mtommodel->loadAny();
        self::assertEquals($mtommodel->get('some_other_field'), 'LALA');
    }

    public function testMToMModelIsReturned()
    {
        $persistence = new Persistence\Array_();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $res = $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertInstanceOf(StudentToLesson::class, $res);
        $res = $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertInstanceOf(StudentToLesson::class, $res);
    }

    public function testOnAfterDeleteHookDeletesMToMModel()
    {
        $persistence = new Persistence\Array_();
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
}