<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM;

use Atk4\Data\Exception;
use Atk4\Data\Model;
use Throwable;


abstract class JunctionModel extends Model
{

    /**
     * @var  array<string,class-string|null> $relationFieldNames
     * with 2 keys and 2 values. Set these four strings child classes.
     * Will be used to create hasOne Reference Fields in init()
     * e.g. [
     *          'student_id' => Student::class,
     *          'lesson_id' => Lesson::class
     *      ]
     */
    protected static array $relationFieldNames = [];

    /**
     * @var array<class-string,Model|null> $referencedEntities
     * containing instances of the two linked models. Useful for re-using them and saving DB requests
     */
    protected array $referencedEntities = [];


    /**
     *  check if $fieldNamesForReferencedClasses content is valid.
     *  Create hasOne References to both linked classes.
     *
     * @return void
     * @throws Exception
     */
    protected function init(): void
    {
        parent::init();
        //make sure 2 classes to link are defined
        if (count(static::$relationFieldNames) !== 2) {
            throw new Exception(
                '2 Fields and corresponding classes need to be defined in fieldNamesForReferencedClasses array'
            );
        }
        if (
            !class_exists(reset(static::$relationFieldNames))
            || !class_exists(end(static::$relationFieldNames))
        ) {
            throw new Exception('Non existent Class defined in $relationFieldNames array');
        }

        foreach (static::$relationFieldNames as $fieldName => $className) {
            /** @var class-string $className */
            $this->hasOne($fieldName, ['model' => [$className], 'required' => true]);
            $this->referencedEntities[$className] = null;
        }
    }

    /**
     *  Shortcut to get an entity from each of the linked classes. e.g.
     *  $studentToLesson = StudentToLesson::addMToMRelation($student, 4);
     *  $lesson = $studentToLesson->getReferenceEntity(Lesson::class); //will return Lesson record with ID 4
     *  the requested entity is returned without an extra DB request once it was loaded, e.g. by addMToMRelation()
     *
     * @param class-string<Model> $className
     * @return Model
     * @throws Exception
     */
    public function getReferencedEntity(string $className): Model
    {
        $this->assertIsEntity();
        if (!array_key_exists($className, $this->referencedEntities)) {
            throw new Exception('Invalid className passed in ' . __FUNCTION__);
        }

        //load if necessary
        if ($this->referencedEntities[$className] === null) {
            $model = new $className($this->getModel()->getPersistence());
            //will throw exception if record isn't found
            $this->referencedEntities[$className] = $model->load(
                $this->get(array_search($className, static::$relationFieldNames))
            );
        }

        return $this->referencedEntities[$className];
    }

    /**
     *  used to make records available in getReferencedEntity() without extra DB request
     *
     * @param Model $entity
     * @return void
     * @throws Exception
     */
    public function addReferencedEntity(Model $entity): void
    {
        $entity->assertIsEntity();
        $modelClass = get_class($entity);
        if (!array_key_exists($modelClass, $this->referencedEntities)) {
            throw new Exception('This ' . __CLASS__ . ' does not have a reference to ' . $modelClass);
        }

        $this->referencedEntities[$modelClass] = $entity;
    }

    /**
     *  used to get the correct field name that corresponds to one of the linked Models
     *
     * @param Model $model
     * @return string
     * @throws Exception
     */
    public function getFieldNameForModel(Model $model): string
    {
        $fieldName = array_search(get_class($model), static::$relationFieldNames);
        if (!$fieldName) {
            throw new Exception(
                'No field name defined in $fieldNamesForReferencedEntities for Class ' . get_class($model)
            );
        }

        return $fieldName;
    }


    /**
     * results ín e.g. $this->addCondition('student_id', 5);
     *
     * @param Model $entity
     * @return void
     * @throws Exception
     */
    protected function addConditionForModel(Model $entity): void
    {
        $entity->assertIsEntity();
        $this->addCondition($this->getFieldNameForModel($entity), $entity->getId());
    }


    /**
     * We have 2 Model classes defined which the JunctionModel will connect. This function returns the class name of
     * the other class if one is passed.
     *
     * @param Model $model
     * @return class-string<Model>
     * @throws Exception
     */
    public static function getOtherModelClass(Model $model): string
    {
        $modelClass = get_class($model);
        if (!in_array($modelClass, static::$relationFieldNames)) {
            throw new Exception(
                'Model ' . $modelClass . ' is not one of the Models having an MToM relation via ' . __CLASS__
            );
        }

        //as array has 2 elements, return second if passed class is the first, else otherwise
        if (reset(static::$relationFieldNames) === $modelClass) {
            return end(static::$relationFieldNames);
        }
        return reset(static::$relationFieldNames);
    }

    /**
     * Used to load the other model if only ID was passed.
     * Make sure passed model is of the correct class.
     * Check other model is loaded so its ID can be used.
     *
     * @param Model $entity
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @return Model
     * @throws Exception
     */
    protected static function getOtherEntity(Model $entity, Model|int $otherEntity): Model
    {
        $entity->assertIsLoaded();
        $otherModelClass = self::getOtherModelClass($entity);
        if (is_object($otherEntity)) {
            //only check if it's a model of the correct class; also check if accidentally $this was passed
            if (get_class($otherEntity) !== $otherModelClass) {
                throw new Exception(
                    'Object of wrong class was passed: ' . $otherModelClass
                    . 'expected, ' . get_class($otherEntity) . ' passed.'
                );
            }
        } else {
            $id = $otherEntity;
            $otherEntity = new $otherModelClass($entity->getModel()->getPersistence());
            $otherEntity = $otherEntity->tryLoad($id);
        }

        //make sure entity is loaded
        if (!$otherEntity || !$otherEntity->isLoaded()) {
            throw new Exception('otherEntity could not be loaded in ' . __FUNCTION__);
        }

        return $otherEntity;
    }


    /**
     *  Create a new MToM relation, e.g. a new StudentToLesson record.
     *  First checks if record does exist already, and only then adds new relation.
     *
     * @param Model $entity
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @param array<string,mixed> $additionalFields
     * @return JunctionModel
     * @throws Exception
     * @throws \Atk4\Core\Exception|Throwable
     */
    public static function addMToMRelation(
        Model $entity,
        Model|int $otherEntity,
        array $additionalFields = []
    ): static {
        $otherEntity = self::getOtherEntity($entity, $otherEntity);

        $mToMModel = new static($entity->getModel()->getPersistence());
        //check if reference already exists, if so update existing record only
        $mToMModel->addConditionForModel($entity);
        $mToMModel->addConditionForModel($otherEntity);
        //no reload necessary after insert
        $mToMModel->reloadAfterSave = false;
        $mToMEntity = $mToMModel->tryLoadAny() ?? $mToMModel->createEntity();

        $mToMEntity->set($mToMEntity->getFieldNameForModel($entity), $entity->getId());
        $mToMEntity->set($mToMEntity->getFieldNameForModel($otherEntity), $otherEntity->getId());

        //set additional field values
        foreach ($additionalFields as $fieldName => $value) {
            $mToMEntity->set($fieldName, $value);
        }

        //if that record already exists mysql will throw an error if unique index is set, catch here
        $mToMEntity->save();
        $mToMEntity->addReferencedEntity($entity);
        $mToMEntity->addReferencedEntity($otherEntity);

        return $mToMEntity;
    }


    /**
     *  method used to remove a MToMModel record like StudentToLesson.
     *
     * @param Model $entity
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @return JunctionModel
     * @throws Exception
     */
    public static function removeMToMRelation(Model $entity, int|Model $otherEntity): JunctionModel
    {
        //$this needs to be loaded to get ID
        $entity->assertIsLoaded();
        $otherEntity = self::getOtherEntity($entity, $otherEntity);

        $mToMModel = new static($entity->getModel()->getPersistence());
        $mToMModel->addConditionForModel($entity);
        $mToMModel->addConditionForModel($otherEntity);
        //loadAny as it will throw exception when record is not found
        $mToMentity = $mToMModel->loadAny();
        $mToMentity->delete();

        return $mToMentity;
    }


    /**
     * checks if a MtoM reference to the given entity exists or not, e.g. if a StudentToLesson record exists for a
     * specific student and lesson
     *
     * @param Model $entity
     * @param int|Model $otherEntity //if int, then it's only an ID
     * @return bool
     * @throws Exception
     */
    public static function hasMToMRelation(Model $entity, int|Model $otherEntity): bool
    {
        $entity->assertIsLoaded();
        $otherEntity = self::getOtherEntity($entity, $otherEntity);

        $mToMModel = new static($entity->getModel()->getPersistence());
        $mToMModel->addConditionForModel($entity);
        $mToMModel->addConditionForModel($otherEntity);
        $mToMEntity = $mToMModel->tryLoadAny();

        return $mToMEntity !== null;
    }
}
