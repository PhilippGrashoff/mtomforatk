<?php declare(strict_types=1);

namespace mtomforatk\tests;


use mtomforatk\tests\testmodels\Lesson;
use mtomforatk\tests\testmodels\Student;
use mtomforatk\tests\testmodels\StudentToLesson;
use atk4\core\AtkPhpunit\TestCase;
use atk4\data\Persistence;
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
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        //adding again shouldnt create a new record
       $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }


    /**
     * see if $this not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionThisNotLoaded() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $b->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * see if $object not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionObjectNotLoaded() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * test adding by id
     */
    public function testMToMAddingById() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($a, 'addMToMRelation', $b->get('id'), new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());
    }


    /**
     * test adding by invalid id
     */
    public function testMToMAddingByInvalidId() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $a->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'addMToMRelation', 11111, new StudentToLesson($persistence));
    }


    /**
     * Tests the MToM removal functionality
     */
    public function testMToMRemoval() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        $this->callProtected($a, 'removeMToMRelation', $b, new StudentToLesson($persistence));
        //should be removed
        self::assertEquals($mtom_count, (new StudentToLesson($persistence))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        self::expectException(Exception::class);
        $this->callProtected($a, 'removeMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionThisNotLoaded() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $b->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'removeMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionObjectNotLoaded() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'removeMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * test hasMToM
     */
    public function testHasMToMReference() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));

        self::assertTrue($this->callProtected($a, 'hasMToMRelation', $b, new StudentToLesson($persistence)));
        self::assertTrue($this->callProtected($b, 'hasMToMRelation', $a, new StudentToLesson($persistence)));

        $this->callProtected($a, 'removeMToMRelation', $b, new StudentToLesson($persistence));
        self::assertFalse($this->callProtected($a, 'hasMToMRelation', $b, new StudentToLesson($persistence)));
        self::assertFalse($this->callProtected($b, 'hasMToMRelation', $a, new StudentToLesson($persistence)));
    }


    /**
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testhasMToMRelationThrowExceptionThisNotLoaded() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $b->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'hasMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testhasMToMRelationThrowExceptionObjectNotLoaded() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();

        self::expectException(Exception::class);
        $this->callProtected($a, 'hasMToMRelation', $b, new StudentToLesson($persistence));
    }


    /**
     * see if exception is thrown when wrong class type is passed in MToMAdding
     */
    public function testMToMAddingWrongClassException() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $a->save();
        self::expectException(Exception::class);
        $this->callProtected($a, 'addMToMRelation', $a, new StudentToLesson($persistence));
    }


    /*
     * see if exception is thrown when wrong class type is passed in MToMRemoval
     */
    public function testMToMRemovalWrongClassException() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
        self::expectException(Exception::class);
        $this->callProtected($a, 'removeMToMRelation', $a, new StudentToLesson($persistence));
    }


    /*
     * see if exception is thrown when wrong class type is passed in HasMToM
     */
    public function testhasMToMRelationWrongClassException() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
        self::expectException(Exception::class);
        $this->callProtected($a, 'hasMToMRelation', $a, new StudentToLesson($persistence));
    }


    /*
     *
     */
    public function testAddAdditionalFields() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();

        $mtom_count = (new StudentToLesson($persistence))->action('count')->getOne();
        $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence), ['name' => 'LALA']);
        self::assertEquals($mtom_count + 1, (new StudentToLesson($persistence))->action('count')->getOne());

        $mtommodel = new StudentToLesson($persistence);
        $mtommodel->setOrder('id desc');
        $mtommodel->setLimit(0,1);
        foreach($mtommodel as $m) {
            self::assertEquals($m->get('test1'), 'LALA');
        }
    }


    /**
     *
     */
    public function testMToMModelIsReturned() {
        $persistence = new Persistence\Array_();
        $a = new Student($persistence);
        $b = new Lesson($persistence);
        $a->save();
        $b->save();

        $res = $this->callProtected($a, 'addMToMRelation', $b, new StudentToLesson($persistence));
        self::assertInstanceOf(StudentToLesson::class, $res);
        $res = $this->callProtected($a, 'removeMToMRelation', $b, new StudentToLesson($persistence));
        self::assertInstanceOf(StudentToLesson::class, $res);
    }
}