<?php

namespace AppGear\PlatformBundle\Storage\Neo4j;

use AppGear\PlatformBundle\Entity\Model;
use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Everyman\Neo4j\Node;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Storage
{
    /**
     * Neo4j client
     *
     * @var Client
     */
    protected $client;

    /**
     * Normalizer
     *
     * @var GetSetMethodNormalizer
     */
    protected $normalizer;

    /**
     * The application unique ID to the data separating in the database
     *
     * @var string
     */
    protected $applicationId;

    /**
     * Constructor
     *
     * @param Client $client Neo4j client
     * @param GetSetMethodNormalizer $normalizer Normalizer
     * @param string $applicationId The application unique ID to the data separating in the database
     */
    public function __construct(Client $client, GetSetMethodNormalizer $normalizer, $applicationId)
    {
        $this->client        = $client;
        $this->normalizer    = $normalizer;
        $this->applicationId = $applicationId;
    }


    /** Execute query and create an objects for the nodes
     *
     * @param Model|string $model The model instance or the model full name
     * @param string $cypher A Cypher query string or template
     * @param array $vars Replacement vars to inject into the query
     *
     * @return object[] The entities instances
     */
    protected function executeAndInstance($model, $cypher, array $vars = [])
    {
        $result = [];

        // Create the query object
        $query = new Query($this->client, $cypher, $vars);

        // Execute query and get result
        $resultSet = $query->getResultSet();

        foreach ($resultSet as $node) {

            // Look for the most suitable class
            $suitableModelName = $this->lookMostSuitableModel($node, $model);

            $result[] = $this->normalizer->denormalize($node->getProperties(), $suitableModelName);
        }

        return $result;
    }


    /**
     * Finds an entity by its identifier.
     *
     * @param Model|string $model The model instance or the model full name
     * @param mixed $id The identifier
     *
     * @return null|object The entity instance or NULL if the entity can not be found.
     */
    public function find($model, $id)
    {
        if ($model instanceof Model) {
            $model = $model->getFullName();
        }

        $cypher = 'MATCH (n:`{label}`) WHERE ID(n) = {id} AND n:`{applicationId}` RETURN n';

        $result = $this->executeAndInstance($model, $cypher, ['label' => $model, 'id' => $id, 'applicationId' => $this->applicationId]);

        if (count($result) === 0) {
            return null;
        }

        return $result[0];
    }


    /**
     * Finds all entities
     *
     * @param Model|string $model The model instance or the model full name
     *
     * @return object[] The entities instances
     */
    public function findAll($model)
    {
        if ($model instanceof Model) {
            $model = $model->getFullName();
        }

        $cypher = 'MATCH (n:`{label}`) WHERE  n:`{applicationId}` RETURN n';

        return $this->executeAndInstance($model, $cypher, ['label' => $model]);
    }


    /**
     * Finds the entities related from the entity with same ID
     *
     * @param Model|string $model The model instance or the model full name
     * @param mixed $id The identifier
     * @param string $relationship The relationship name
     *
     * @return object[] The entities instances
     */
    public function findRelated($model, $id, $relationship)
    {
        if ($model instanceof Model) {
            $model = $model->getFullName();
        }

        $cypher = 'MATCH (n:`{label}`)-[:{relationship}]->(m) WHERE ID(n) = {id} AND n:`{applicationId}` RETURN m';

        return $this->executeAndInstance($model, $cypher, ['label' => $model, 'relationship' => $relationship, 'id' => $id, 'applicationId' => $this->applicationId]);
    }


    /**
     * Finds the entities related to the entity with same ID
     *
     * @param Model|string $model The model instance or the model full name
     * @param mixed $id The identifier
     * @param string $relationship The relationship name
     *
     * @return object[] The entities instances
     */
    public function findRelatedTo($model, $id, $relationship)
    {
        if ($model instanceof Model) {
            $model = $model->getFullName();
        }

        $cypher = 'MATCH (n:`{label}`)<-[:{relationship}]->(m) WHERE ID(n) = {id} AND n:`{applicationId}` RETURN m';

        return $this->executeAndInstance($model, $cypher, ['label' => $model, 'relationship' => $relationship, 'id' => $id, 'applicationId' => $this->applicationId]);
    }


    /**
     * Look for the most suitable class for the node
     *
     * Model classes stored in the node labels - the most "deep" class is the most suitable class
     *
     * @param Node $node Node
     * @param string $model The queried model name
     *
     * @return string The most suitable model name (class name)
     */
    protected function lookMostSuitableModel(Node $node, $model)
    {
        foreach ($node->getLabels() as $label) {
            if (strpos($label->getName(), $model) === 0) {
                if (substr_count($label->getName(), '\\') > substr_count($model, '\\')) {
                    $model = $label->getName();
                }
            }
        }

        return $model;
    }
}