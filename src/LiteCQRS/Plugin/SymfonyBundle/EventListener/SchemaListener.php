<?php

namespace LiteCQRS\Plugin\SymfonyBundle\EventListener;

use LiteCQRS\Plugin\Doctrine\EventStore\TableEventStore;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use LiteCQRS\Plugin\Doctrine\EventStore\TableEventStoreSchema;

class SchemaListener
{
    private $schema;

    public function __construct(TableEventSchema $schema)
    {
        $this->schema = $schema;
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
    	$schema = $args->getSchema();
    	if (!$schema->hasTable($this->schema->getTableName())) {
	        $schema->createTable($this->schema->getTableSchema());
	    }
    }
}
