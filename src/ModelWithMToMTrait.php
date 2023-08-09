<?php declare(strict_types=1);

namespace mtomforatk;

use Atk4\Data\Model;
use Atk4\Data\Reference;


/**
 * @extends Model<Model>
 */
trait ModelWithMToMTrait
{

    /**
     *  Create a new MToM relation, e.g. a new StudentToLesson record. Called from either Student or Lesson class.
     *  First checks if record does exist already, and only then adds new relation.
     *
     * @param MToMModel $mToMModel
     * @param $otherModel
     * @param array<string,mixed> $additionalFields
     * @return MToMModel
     * @throws \Atk4\Data\Exception
     * @throws \Atk4\Core\Exception
     */
    public function addMToMRelation(MToMModel $mToMModel, $otherModel, array $additionalFields = []): MToMModel
    {
        //$this needs to be loaded to get ID
        $this->assertIsLoaded();
        $otherModel = $this->getOtherEntity($otherModel, $mToMModel);
        //check if reference already exists, if so update existing record only
        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        $mToMModel->tryLoadAny();

        //set values
        $mToMModel->set($mToMModel->getFieldNameForModel($this), $this->get('id'));
        $mToMModel->set($mToMModel->getFieldNameForModel($otherModel), $otherModel->get('id'));

        //set additional field values
        foreach ($additionalFields as $fieldName => $value) {
            $mToMModel->set($fieldName, $value);
        }

        //no reload necessary after insert
        $mToMModel->reload_after_save = false;
        //if that record already exists mysql will throw an error if unique index is set, catch here
        $mToMModel->save();
        $mToMModel->addReferenceEntity($this);
        $mToMModel->addReferenceEntity($otherModel);

        return $mToMModel;
    }


    /**
     *  method used to remove a MToMModel record like StudentToLesson. Either used from Student or Lesson class.
     *  GuestToGroup etc.
     *
     * @param MToMModel $mToMModel
     * @param $otherModel
     * @return MToMModel
     */
    public function removeMToMRelation(MToMModel $mToMModel, $otherModel): MToMModel
    {
        //$this needs to be loaded to get ID
        $this->assertIsLoaded();
        $otherModel = $this->getOtherEntity($otherModel, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        //loadAny as it will throw exception when record is not found
        $mToMModel->loadAny();
        $mToMModel->delete();

        return $mToMModel;
    }


    /**
     * checks if a MtoM reference to the given object exists or not, e.g. if a StudentToLesson record exists for a
     * specific student and lesson
     *
     * @param MToMModel $mToMModel
     * @param $otherModel
     * @return bool
     */
    public function hasMToMRelation(MToMModel $mToMModel, $otherModel): bool
    {
        $this->assertIsLoaded();
        $otherModel = $this->getOtherEntity($otherModel, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        $mToMModel->tryLoadAny();

        return $mToMModel->loaded();
    }

    /**
     * 1) adds HasMany Reference to intermediate model.
     * 2) adds after delete hook which deletes any intermediate model linked to the deleted "main" model.
     * This way, no outdated intermediate models exist.
     * Returns HasMany reference for further modifying reference if needed.
     *
     * @param string $mtomClassName
     * @param string $referenceName
     * @param array $referenceDefaults
     * @param array $mtomClassDefaults
     * @return Reference\HasMany
     */
    protected function addMToMReferenceAndDeleteHook(
        string $mtomClassName,
        string $referenceName = '',
        array $referenceDefaults = [],
        array $mtomClassDefaults = []
    ): Reference\HasMany {
        //if no reference name was passed, use Class name
        if (!$referenceName) {
            $referenceName = $mtomClassName;
        }

        if (!class_exists($mtomClassName)) {
            throw new Exception('Class ' . $mtomClassName . ' not found in ' . __FUNCTION__);
        }

        $reference = $this->hasMany(
            $referenceName,
            array_merge(['model' => array_merge([$mtomClassName], $mtomClassDefaults)], $referenceDefaults)
        );
        $this->onHook(
            Model::HOOK_BEFORE_DELETE,
            function ($model) use ($referenceName): void {
                foreach ($model->ref($referenceName) as $mtomModel) {
                    $mtomModel->delete();
                }
            }
        );

        return $reference;
    }


    /**
     * Used to load the other model if only ID was passed.
     * Make sure passed model is of the correct class.
     * Check other model is loaded so id can be gotten.
     *
     * @param $otherModel
     * @param MToMModel $mToMModel
     * @return Model
     */
    protected function getOtherEntity($otherModel, MToMModel $mToMModel): Model
    {
        $otherModelClass = $mToMModel->getOtherModelClass($this);
        if (is_object($otherModel)) {
            //only check if it's a model of the correct class; also check if accidentally $this was passed
            if (get_class($otherModel) !== $otherModelClass) {
                throw new Exception(
                    'Object of wrong class was passed: ' . $mToMModel->getOtherModelClass($this)
                    . 'expected, ' . get_class($otherModel) . ' passed.'
                );
            }
        } else {
            $id = $otherModel;
            $otherModel = new $otherModelClass($this->persistence);
            $otherModel->tryLoad($id);
        }

        //make sure object is loaded
        if (!$otherModel->loaded()) {
            throw new Exception('Object could not be loaded in ' . __FUNCTION__);
        }

        return $otherModel;
    }
}