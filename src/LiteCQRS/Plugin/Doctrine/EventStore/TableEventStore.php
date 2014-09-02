<?php

namespace LiteCQRS\Plugin\Doctrine\EventStore;

use LiteCQRS\DomainEvent;
use LiteCQRS\EventStore\EventStoreInterface;
use LiteCQRS\EventStore\SerializerInterface;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

/**
 * Store events in a database table using Doctrine DBAL.
 */
class TableEventStore implements EventStoreInterface
{
    private $conn;
    private $table;
    private $serializer;

    public function __construct(Connection $conn, SerializerInterface $serializer, $table = 'litecqrs_events')
    {
        $this->conn       = $conn;
        $this->serializer = $serializer;
        $this->table      = $table;
    }

    /**
     * Add Event into the event_Store table.
     * @param DomainEvent $event
     * @return void
     */

    public function store(DomainEvent $event)
    {
        $header = $event->getMessageHeader();

        $aggregateId = !is_array($header->aggregateId) ? $header->aggregateId : json_encode($header->aggregateId);

        $this->conn->insert($this->table, array(
            'event_id'       => $header->id,
            'aggregate_type' => $header->aggregateType,
            'aggregate_id'   => $aggregateId,
            'event'          => $event->getEventName(),
            'event_date'     => $header->date->format('Y-m-d H:i:s'),// looses microseconds precision
            'command_id'     => $header->commandId,
            'session_id'     => $header->sessionId,
            'data'           => $this->serializer->serialize($event, 'json'),
        ));
    }
    
    /**
     * Load all event for a aggregate orderd by date.
     * @param string $class
     * @param string $id
     * @return array
     */
    public function find($class,$id){
        $stmt = $this->conn->executeQuery("SELECT * FROM ".$this->table." WHERE aggregate_type = :class AND aggregate_id = :id ORDER BY event_date ASC",array("class"=>$class, "id"=>$id));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}

