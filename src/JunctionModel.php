<?php declare(strict_types=1);

namespace PhilippR\Atk4\MToM;

use Atk4\Data\Exception;
use Atk4\Data\Model;


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
    protected array $relationFieldNames = [];

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
        if (count($this->relationFieldNames) !== 2) {
            throw new Exception(
                '2 Fields and corresponding classes need to be defined in fieldNamesForReferencedClasses array'
            );
        }
        if (
            !class_exists(reset($this->relationFieldNames))
            || !class_exists(end($this->relationFieldNames))
        ) {
            throw new Exception('Non existent Class defined in $relationFieldNames array');
        }

        foreach ($this->relationFieldNames as $fieldName => $className) {
            /** @var class-string $className */
            $this->hasOne($fieldName, ['model' => [$className], 'required' => true]);
            $this->referencedEntities[$className] = null;
        }
    }


    /**
     *  Shortcut to get an entity from each of the linked classes. e.g.
     *  $studentToLesson = $student->addLesson(4); //add Lesson by ID, no lesson entity yet
     *  $lesson = $studentToLesson->getReferenceEntity(Lesson::class); //will return Lesson record with ID 4
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
                $this->get(array_search($className, $this->relationFieldNames))
            );
        }

        return $this->referencedEntities[$className];
    }


    /**
     *  used by MToMTrait to make records available in getReferencedEntity() without extra DB request
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
     *  used by MToMTrait to get the correct field name that corresponds to one of the linked Models
     *
     * @param Model $model
     * @return string
     * @throws Exception
     */
    public function getFieldNameForModel(Model $model): string
    {
        $fieldName = array_search(get_class($model), $this->relationFieldNames);
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
    public function addConditionForModel(Model $entity): void
    {
        $entity->assertIsEntity();
        $this->addCondition($this->getFieldNameForModel($entity), $entity->getId());
    }


    /**
     * We have 2 Model classes defined which the JunctionModel will connect. This function returns the class name of
     * the other class if one is passed
     *
     * @param Model $model
     * @return class-string<Model>
     * @throws Exception
     */
    public function getOtherModelClass(Model $model): string
    {
        $modelClass = get_class($model);
        if (!in_array($modelClass, $this->relationFieldNames)) {
            throw new Exception('Class ' . $modelClass . 'not found in fieldNamesForReferencedClasses');
        }

        //as array has 2 elements, return second if passed class is the first, else otherwise
        if (reset($this->relationFieldNames) === $modelClass) {
            return end($this->relationFieldNames);
        }
        return reset($this->relationFieldNames);
    }
}
