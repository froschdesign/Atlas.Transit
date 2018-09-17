<?php
declare(strict_types=1);

namespace Atlas\Transit\Domain\Entity\Reply;

use Atlas\Mapper\Record;
use Atlas\Transit\DataConverter;
use Atlas\Transit\Domain\Value\DateTime;

class ReplyDataConverter extends DataConverter
{
    public function fromSourceToDomain($record, array &$parameters) : void
    {
        $parameters['createdAt'] = new DateTime('1979-11-07');
    }
}