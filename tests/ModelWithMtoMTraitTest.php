<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\tests\phpunit\TestCase;

/**
 * Class MToMTraitTest
 * @package PMRAtk\tests\phpunit\Data\Traits
 */
class MToMTraitTest extends TestCase {

    /*
     * Tests the MToM adding functionality
     */
    public function testMToMAdding() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new AToB(self::$app->db))->action('count')->getOne();
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->assertEquals($mtom_count + 1, (new AToB(self::$app->db))->action('count')->getOne());

        //adding again shouldnt create a new record
       $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->assertEquals($mtom_count + 1, (new AToB(self::$app->db))->action('count')->getOne());
    }


    /*
     * see if $this not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionThisNotLoaded() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $object not loaded throws exception in adding MTOm
     */
    public function testMToMAddingThrowExceptionObjectNotLoaded() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * test adding by id
     */
    public function testMToMAddingById() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new AToB(self::$app->db))->action('count')->getOne();
        $this->callProtected($a, '_addMToMRelation', [$b->get('id'), new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->assertEquals($mtom_count + 1, (new AToB(self::$app->db))->action('count')->getOne());
    }


    /*
     * test adding by invalid id
     */
    public function testMToMAddingByInvalidId() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [11111, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * Tests the MToM removal functionality
     */
    public function testMToMRemoval() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new AToB(self::$app->db))->action('count')->getOne();
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->assertEquals($mtom_count + 1, (new AToB(self::$app->db))->action('count')->getOne());

        $this->callProtected($a, '_removeMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        //should be removed
        $this->assertEquals($mtom_count, (new AToB(self::$app->db))->action('count')->getOne());
        //trying to remove again shouldnt work but throw exception
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionThisNotLoaded() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testMToMRemovalThrowExceptionObjectNotLoaded() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * test hasMToM
     */
    public function testHasMToMReference() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);

        $this->assertTrue($this->callProtected($a, '_hasMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertTrue($this->callProtected($b, '_hasMToMRelation', [$a, new AToB(self::$app->db), BaseModelA::class, 'BaseModelB_id', 'BaseModelA_id']));

        $this->callProtected($a, '_removeMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->assertFalse($this->callProtected($a, '_hasMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']));
        $this->assertFalse($this->callProtected($b, '_hasMToMRelation', [$a, new AToB(self::$app->db), BaseModelA::class, 'BaseModelB_id', 'BaseModelA_id']));
    }


    /*
     * see if $this not loaded throws exception in removing MTOm
     */
    public function testMToMHasThrowExceptionThisNotLoaded() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $b->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_hasMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if $object not loaded throws exception in removing MTOm
     */
    public function testMToMHasThrowExceptionObjectNotLoaded() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();

        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_hasMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if exception is thrown when wrong class type is passed in MToMAdding
     */
    public function testMToMAddingWrongClassException() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_addMToMRelation', [$a, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if exception is thrown when wrong class type is passed in MToMRemoval
     */
    public function testMToMRemovalWrongClassException() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_removeMToMRelation', [$a, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     * see if exception is thrown when wrong class type is passed in HasMToM
     */
    public function testMToMHasWrongClassException() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($a, '_hasMToMRelation', [$a, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
    }


    /*
     *
     */
    public function testAddAdditionalFields() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $mtom_count = (new AToB(self::$app->db))->action('count')->getOne();
        $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id', ['test1' => 'LALA']]);
        $this->assertEquals($mtom_count + 1, (new AToB(self::$app->db))->action('count')->getOne());

        $mtommodel = new AToB(self::$app->db);
        $mtommodel->setOrder('id desc');
        $mtommodel->setLimit(0,1);
        foreach($mtommodel as $m) {
            $this->assertEquals($m->get('test1'), 'LALA');
        }
    }


    /**
     *
     */
    public function testMToMModelIsReturned() {
        $a = new BaseModelA(self::$app->db);
        $b = new BaseModelB(self::$app->db);
        $a->save();
        $b->save();

        $res = $this->callProtected($a, '_addMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id', ['test1' => 'LALA']]);
        self::assertInstanceOf(AToB::class, $res);
        $res = $this->callProtected($a, '_removeMToMRelation', [$b, new AToB(self::$app->db), BaseModelB::class, 'BaseModelA_id', 'BaseModelB_id']);
        self::assertInstanceOf(AToB::class, $res);
    }
}