<?php

namespace Core\Classes\Providers\DB;


class QueryResult
{
    protected $stmt = null;
    protected $time = 0;
    protected $errorInfo = null;
    protected $nbLigne = null;
    protected $qb = null;

    public function __construct(array $data)
    {
    	$this->stmt = $data['stmt'] ?? null;
        $this->time = $data['time'] ?? null;
        $this->errorInfo = $data['errorInfo'] ?? null;
        $this->nbLigne = $data['nbLigne'] ?? null;
        $this->qb = $data['qb'] ?? null;

		\Debug::sql($this->qb->getQuery(),$this->time,$this->errorInfo == null ? '' : implode(' - ', $this->errorInfo),$this->qb->getParams());
    }

	public function getStmt()
	{
		return $this->stmt;
	}

	public function hasError(): bool
	{

		return $this->errorInfo !== null;
	}

	public function getError(): ?array
    {
        return $this->errorInfo;
    }

    public function getNbLigne()
    {
        return $this->nbLigne;
    }
}