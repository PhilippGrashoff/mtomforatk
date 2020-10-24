<?php declare(strict_types=1);

namespace mtomforatk;

use atk4\data\Exception;
use atk4\data\Model;
use atk4\data\Reference;


/**
 * Trait MToMTrait
 */
trait ModelWithMToMTrait
{

    /**
     * Create a new MToM relation, e.g. a new StudentToLesson record. Called from either Student or Lesson class.
     * First checks if record does exist already, and only then adds new relation.
     */
    public function addMToMRelation(MToMModel $mToMModel, $otherModel, array $additionalFields = []): MToMModel
    {
        //$this needs to be loaded to get ID
        $this->checkThisIsLoaded();
        $otherModel = $this->getOtherModelRecord($otherModel, $mToMModel);
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
        $mToMModel->addLoadedObject($this);
        $mToMModel->addLoadedObject($otherModel);

        return $mToMModel;
    }


    /**
     * function used to remove a MToMModel record like StudentToLesson. Either used from Student or Lesson class.
     * GuestToGroup etc.
     */
    public function removeMToMRelation(MToMModel $mToMModel, $otherModel): MToMModel
    {
        //$this needs to be loaded to get ID
        $this->checkThisIsLoaded();
        $otherModel = $this->getOtherModelRecord($otherModel, $mToMModel);

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
     */
    public function hasMToMRelation(MToMModel $mToMModel, $otherModel): bool
    {
        $this->checkThisIsLoaded();
        $otherModel = $this->getOtherModelRecord($otherModel, $mToMModel);

        $mToMModel->addConditionForModel($this);
        $mToMModel->addConditionForModel($otherModel);
        $mToMModel->tryLoadAny();

        return $mToMModel->loaded();
    }


    /**
     * In each MToM operation, $this needs to be loaded to pull id. This function throws an exception if its not.
     */
    protected function checkThisIsLoaded(): void
    {
        if (!$this->loaded()) {
            throw new Exception(
                '$this needs to be loaded in ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function']
            );
        }
    }


    /**
     * 1) adds HasMany Reference to intermediate model.
     * 2) adds after delete hook which deletes any intermediate model linked to the deleted "main" model.
     *    This way, no outdated intermediate models exist.
     * Returns HasMany reference for further modifying reference if needed.
     */
    protected function addMToMReferenceAndDeleteHook(
        string $mtomClassName,
        string $referenceName = '',
        array $referenceDefaults = []
    ): Reference\HasMany {
        //if no reference name was passed, use Class name
        if (!$referenceName) {
            $referenceName = $mtomClassName;
        }

        if(!class_exists($mtomClassName)) {
            throw new Exception('Class ' . $mtomClassName . ' not found in ' . __FUNCTION__);
        }

        $reference = $this->hasMany($referenceName, array_merge([$mtomClassName], $referenceDefaults));
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
     * Used to load the other model if only Id was passed.
     * Make sure passed model is of the correct class.
     * Check other model is loaded so id can be gotten.
     */
    protected function getOtherModelRecord($otherModel, MToMModel $mToMModel): Model
    {
        $otherModelClass = $mToMModel->getOtherModelClass($this);
        if (is_object($otherModel)) {
            //only check if its a model of the correct class; also check if accidently $this was passed
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