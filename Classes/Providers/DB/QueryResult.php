<?php

namespace Core\Classes\Providers\DB;


class QueryResult
{
    protected $stmt = null;
    protected $time = 0;
    protected $errorInfo = null;
    protected $errorCode = null;
    protected $nbLigne = null;
    protected $qb = null;

    public function __construct(array $data)
    {
    	$this->stmt = $data['stmt'] ?? null;
        $this->time = $data['time'] ?? null;
        $this->errorInfo = $data['errorInfo'] ?? null;
        $this->errorCode = $data['errorCode'] ?? null;
        $this->nbLigne = $data['nbLigne'] ?? null;
        $this->qb = $data['qb'] ?? null;

		\Debug::sqldata($data);
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