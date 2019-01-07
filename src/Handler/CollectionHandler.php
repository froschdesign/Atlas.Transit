<?php
declare(strict_types=1);

namespace Atlas\Transit\Handler;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;
use Atlas\Transit\Transit;
use Closure;
use ReflectionClass;
use SplObjectStorage;

class CollectionHandler extends Handler
{
    protected $memberClass;

    public function __construct(
        string $domainClass,
        Mapper $mapper,
        HandlerLocator $handlerLocator
    ) {
        parent::__construct($domainClass, $mapper, $handlerLocator);
        $this->memberClass = substr($domainClass, 0, -10); // strip Collection from class name
    }

    public function newSource($domain, SplObjectStorage $storage, SplObjectStorage $refresh) : object
    {
        $source = $this->mapper->newRecordSet();
        $storage->attach($domain, $source);
        $refresh->attach($domain);
        return $source;
    }

    /**
     * @todo Allow for different member classes based on Record types/values.
     */
    public function getMemberClass(Record $record) : string
    {
        return $this->memberClass;
    }

    public function newDomain($recordSet, SplObjectStorage $storage)
    {
        $members = [];
        foreach ($recordSet as $record) {
            $memberClass = $this->getMemberClass($record);
            $memberHandler = $this->handlerLocator->get($memberClass);
            $members[] = $memberHandler->newDomain($record, $storage);
        }

        $domainClass = $this->domainClass;
        $domain = new $domainClass($members);
        $storage->attach($domain, $recordSet);
        return $domain;
    }

    public function updateSource(object $domain, SplObjectStorage $storage, SplObjectStorage $refresh)
    {
        if (! $storage->contains($domain)) {
            $source = $this->newSource($domain, $storage, $refresh);
        }

        $recordSet = $storage[$domain];
        $recordSet->detachAll();

        foreach ($domain as $member) {
            $handler = $this->handlerLocator->get($member);
            $record = $handler->updateSource($member, $storage, $refresh);
            $recordSet[] = $record;
        }

        return $recordSet;
    }

    public function refreshDomain(object $collection, $recordSet, SplObjectStorage $storage, SplObjectStorage $refresh)
    {
        foreach ($collection as $member) {
            $handler = $this->handlerLocator->get($member);
            $source = $storage[$member];
            $handler->refreshDomain($member, $source, $storage, $refresh);
        }

        $refresh->detach($collection);
    }
}
