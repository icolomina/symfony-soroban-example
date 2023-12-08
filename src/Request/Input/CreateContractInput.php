<?php

namespace App\Request\Input;

class CreateContractInput {

    private string $receiver;
    private string $label;
    private ?string $description = null;
    private string $token;

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function setReceiver(string $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }


    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getToken(): string 
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }
}