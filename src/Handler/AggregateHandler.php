<?php
declare(strict_types=1);

namespace Atlas\Transit\Handler;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\Record;
use Atlas\Transit\Inflector;
use Atlas\Transit\Exception;
use Atlas\Transit\Transit;
use Atlas\Transit\Reflection\AggregateReflection;
use ReflectionParameter;
use ReflectionProperty;
use SplObjectStorage;

class AggregateHandler extends EntityHandler
{
    public function isRoot(object $spec) : bool
    {
        if ($spec instanceof ReflectionParameter) {
            $class = $spec->getClass()->getName() ?? '';
            return $this->reflection->rootClass === $class;
        }

        return $this->reflection->rootClass === get_class($spec);
    }

    protected function newDomainArgument(
        ReflectionParameter $rparam,
        Record $record
    ) {
        $name = $rparam->getName();
        $class = $this->reflection->classes[$name];

        // for the Root Entity, create using the entire record
        if ($this->isRoot($rparam)) {
            $rootHandler = $this->handlerLocator->get($this->reflection->rootClass);
            return $rootHandler->newDomain($record);
        }

        // not the Root Entity, use normal creation
        return parent::newDomainArgument($rparam, $record);
    }

    protected function updateSourceField(
        Record $record,
        string $field,
        $datum,
        SplObjectStorage $refresh
    ) : void
    {
        if ($this->isRoot($datum)) {
            $handler = $this->handlerLocator->get($datum);
            $handler->updateSourceFields($datum, $record, $refresh);
            return;
        }

        parent::updateSourceField(
            $record,
            $field,
            $datum,
            $refresh
        );
    }

    protected function refreshDomainProperty(
        ReflectionProperty $prop,
        object $domain,
        $record,
        SplObjectStorage $refresh
    ) : void
    {
        $datum = $prop->getValue($domain);

        // if the property is a Root, process it with the Record itself
        if (is_object($datum) && $this->isRoot($datum)) {
            $handler = $this->handlerLocator->get($datum);
            $handler->refreshDomainProperties($datum, $record, $refresh);
            return;
        }

        parent::refreshDomainProperty($prop, $domain, $datum, $refresh);
    }
}
