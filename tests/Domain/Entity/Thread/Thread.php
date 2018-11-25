<?php
declare(strict_types=1);

namespace Atlas\Transit\Domain\Entity\Thread;

use Atlas\Transit\Domain\Entity\Entity;
use Atlas\Transit\Domain\Entity\Author\Author;
use Atlas\Transit\Domain\Value\DateTime;

class Thread extends Entity
{
    protected $threadId;
    protected $author;
    protected $createdAt;
    protected $subject;
    protected $body;

    public function __construct(
        Author $author,
        DateTime $createdAt,
        string $subject,
        string $body,
        int $threadId = null
    ) {
        $this->author = $author;
        $this->createdAt = $createdAt;
        $this->subject = $subject;
        $this->body = $body;
        $this->threadId = $threadId;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
}
