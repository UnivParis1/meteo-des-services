<?php

namespace App\DTO;

class HistoryDTO {

    public int $id;

    public string $type;

    public string $state;

    public ?string $message;

    public \DateTimeInterface $date;

    public string $author;

    public function __construct(int $id, string $type, string $state, \DateTimeInterface $date, string $author, ?string $message)
    {
        $this->id = $id;
        $this->type = $type;
        $this->state = $state;
        $this->date = $date;
        $this->author = $author;
        $this->message = $message;
    }

    /**
     * Get the value of author.
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set the value of author.
     *
     * @return self
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of date.
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of date.
     *
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get the value of message.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the value of message.
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the value of state.
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of state.
     *
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type.
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
