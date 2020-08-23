<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use atk4\data\Exception;
use atk4\data\Model;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\AToB;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelC;

/**
 * Class MToMModelTest
 * @package PMRAtk\tests\phpunit\Data
 */
class MToMModelTest extends TestCase {

    /**
     *
     */
    public function testInit() {
        $atob = new AToB(self::$app->db);
        self::assertTrue($atob->hasField('BaseModelA_id'));
        self::assertTrue($atob->hasField('BaseModelB_id'));
        self::assertTrue($atob->hasRef('BaseModelA_id'));
        self::assertTrue($atob->hasRef('BaseModelB_id'));
    }


    /**
     *
     */
    public function testAddLoadedObject() {
        $bma = new BaseModelA(self::$app->db);
        $bma->save();
        $atob = new AToB(self::$app->db);
        $atob->addLoadedObject($bma);
        $props = (new \ReflectionClass($atob))->getProperty('referenceObjects');//getProperties(\ReflectionProperty::IS_PROTECTED);
        $props->setAccessible(true);
        $value = $props->getValue($atob);
        self::assertIsArray($value);
        self::assertArrayHasKey(BaseModelA::class, $value);
        self::assertSame(
            $bma,
            $value[BaseModelA::class],
        );
    }


    /**
     *
     */
    public function testAddLoadedObjectExceptionWrongClassPassed() {
        $model = new BaseModelC(self::$app->db);
        $atob = new AToB(self::$app->db);
        self::expectException(Exception::class);
        $atob->addLoadedObject($model);
    }


    /**
     *
     */
    public function testgetObject() {
        $baseModelA = new BaseModelA(self::$app->db);
        $baseModelB = new BaseModelB(self::$app->db);
        $baseModelA->save();
        $baseModelB->save();
        $aToB = new AToB(self::$app->db);

        //A gets loaded from DB
        $aToB->set('BaseModelA_id', $baseModelA->get('id'));
        $resA = $aToB->getObject(BaseModelA::class);
        //different Object but same ID
        self::assertNotSame($baseModelA, $resA);
        self::assertSame($baseModelA->get('id'), $resA->get('id'));

        //B is put in referenceObjects Array, should return same object
        $aToB->addLoadedObject($baseModelB);
        $resB = $aToB->getObject(BaseModelB::class);
        self::assertSame($baseModelB, $resB);
    }


    /**
     *
     */
    public function testgetObjectExceptionInvalidClass() {
        $aToB = new AToB(self::$app->db);
        self::expectException(Exception::class);
        $resA = $aToB->getObject(BaseModelC::class);
    }
}
