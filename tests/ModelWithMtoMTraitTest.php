<?php declare(strict_types=1);

namespace mtomforatk\tests;


use mtomforatk\tests\testmodels\Lesson;
use mtomforatk\tests\testmodels\Student;
use mtomforatk\tests\testmodels\StudentToLesson;
use atk4\core\AtkPhpunit\TestCase;
use mtomforatk\tests\testmodels\TmpPersistenceArray;
use atk4\data\Exception;

/**
 * Class MToMTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class ModelWithMtoMTraitTest extends TestCase {

    /**
     * Tests the MToM adding functionality
     */
    public function testMToMAdding() {
        $persistence = new TmpPersistenceArray();
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


    /**
     * see if $this not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionThisNotLoaded() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
    }


    /**
     * see if $object not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionObjectNotLoaded() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
    }


    /**
     * test adding by id
     */
    public function testMToMAddingById() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($student, 'addMToMRelation', $lesson->get('id'), new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }


    /**
     * test adding by invalid id
     */
    public function testMToMAddingByInvalidId() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', 11111, new StudentToLesson($persistence));
    }


    /**
     * Tests the MToM removal functionality
     */
    public function testMToMRemoval() {
        $persistence = new TmpPersistenceArray();
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


    /**
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionThisNotLoaded() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
    }


    /**
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionObjectNotLoaded() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
    }


    /**
     * test hasMToM
     */
    public function testHasMToMReference() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));

        self::assertTrue($this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence)));
        self::assertTrue($this->callProtected($lesson, 'hasMToMRelation', $student, new StudentToLesson($persistence)));

        $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertFalse($this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence)));
        self::assertFalse($this->callProtected($lesson, 'hasMToMRelation', $student, new StudentToLesson($persistence)));
    }


    /**
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testhasMToMRelationThrowExceptionThisNotLoaded() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $lesson->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence));
    }


    /**
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testhasMToMRelationThrowExceptionObjectNotLoaded() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();

        self::expectException(Exception::class);
        $this->callProtected($student, 'hasMToMRelation', $lesson, new StudentToLesson($persistence));
    }


    /**
     * see if exception is thrown when wrong class type is passed in MToMAdding
     */
    public function testMToMAddingWrongClassException() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $student->save();
        self::expectException(Exception::class);
        $this->callProtected($student, 'addMToMRelation', $student, new StudentToLesson($persistence));
    }


    /*
     * see if exception is thrown when wrong class type is passed in MToMRemoval
     */
    public function testMToMRemovalWrongClassException() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::expectException(Exception::class);
        $this->callProtected($student, 'removeMToMRelation', $student, new StudentToLesson($persistence));
    }


    /*
     * see if exception is thrown when wrong class type is passed in HasMToM
     */
    public function testhasMToMRelationWrongClassException() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::expectException(Exception::class);
        $this->callProtected($student, 'hasMToMRelation', $student, new StudentToLesson($persistence));
    }


    /*
     *
     */
    public function testAddAdditionalFields() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence), ['some_other_field' => 'LALA']);
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        $mtommodel = new StudentToLesson($persistence);
        $mtommodel->loadAny();
        self::assertEquals($mtommodel->get('some_other_field'), 'LALA');

    }


    /**
     *
     */
    public function testMToMModelIsReturned() {
        $persistence = new TmpPersistenceArray();
        $student = new Student($persistence);
        $lesson = new Lesson($persistence);
        $student->save();
        $lesson->save();

        $res = $this->callProtected($student, 'addMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertInstanceOf(StudentToLesson::class, $res);
        $res = $this->callProtected($student, 'removeMToMRelation', $lesson, new StudentToLesson($persistence));
        self::assertInstanceOf(StudentToLesson::class, $res);
    }


    /**
     *
     */
    public function testOnAfterDeleteHookDeletesMToMModel() {
        $persistence = new TmpPersistenceArray();
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