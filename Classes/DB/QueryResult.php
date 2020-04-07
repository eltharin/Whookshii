<?php

namespace Core\Classes\DB;


class QueryResult
{
    protected $errorCode = null;
    protected $errorInfo = null;
    protected $nbLigne = null;
    protected $qb = null;

    public function __construct(array $data)
    {
        $this->errorCode = $data['errorCode'] ?? null;
        $this->errorInfo = $data['errorInfo'] ?? null;
        $this->nbLigne = $data['nbLigne'] ?? null;
        $this->qb = $data['qb'] ?? null;
    }

    public function getError(): bool
    {
        return false;
    }

    public function getNbLigne()
    {
        return $this->nbLigne;
    }


}