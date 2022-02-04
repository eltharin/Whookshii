<?php

namespace Core\Traits;

use Core\App\Mvc\Entity;
use Core\Classes\Providers\DB\QueryBuilder;

trait Intervallaire
{
    protected string $intervallaireFieldBorneMin = '';
    protected string $intervallaireFieldBorneMax = '';
    protected string $intervallaireFieldNiveau = '';
    protected $callable = null;

    public function withCallback($fct)
    {
        $this->callable = $fct;
		return $this;
    }

    private function executeQb($qb)
    {
        if($this->callable !== null)
        {
            call_user_func($this->callable, $qb);
        }

        return $this->provider->execute($qb);
    }

	public function getMasterParent()
	{
		$qb = (new QueryBuilder($this->provider))->select('max(' . $this->intervallaireFieldBorneMax . ') max')->from($this->getTable());

		if($this->callable !== null)
		{
			call_user_func($this->callable, $qb);
		}

		//\HTML::pr($qb);

		$ret = $qb->first();

		if($ret == null)
		{
			$max = 1;
		}
		else
		{
			$max = $ret->max +1;
		}

		return new Entity(['borneMin' => 0, 'borneMax' => $max, 'niveau' => -1]);
	}

    public function addItem(Entity $item, Entity|int|null $parent = null)
    {
		if($parent == null)
		{
			$parent = $this->getMasterParent();
		}
		else
		{
			if(is_int($parent))
			{
				$parent = $this->get($parent);
			}

			$qb1 = (new QueryBuilder())->update($this->getTable())
										->set($this->intervallaireFieldBorneMin, $this->intervallaireFieldBorneMin . ' + 2',true)
										->where($this->intervallaireFieldBorneMin . ' >= ' . $parent->borneMax);

			$qb2 = (new QueryBuilder())->update($this->getTable())
										->set($this->intervallaireFieldBorneMax, $this->intervallaireFieldBorneMax . ' + 2',true)
										->where($this->intervallaireFieldBorneMax . ' >= ' . $parent->borneMax);

			$this->executeQb($qb1);
			$this->executeQb($qb2);
		}

		$item->borneMin = $parent->borneMax;
		$item->borneMax = $parent->borneMax +1 ;
		$item->niveau   = $parent->niveau +1 ;

        $this->DBInsert($item);
        return true;
    }

    public function deleteItem(Entity $item, $withChilds = false)
    {
        if(($item->borneMax - $item->borneMin > 1) && ($withChilds == false))
        {
            return false;
        }

        $qb1 = (new QueryBuilder())->delete($this->getTable(), $this->getPrefixe())
                                    ->where($this->intervallaireFieldBorneMin . ' >= ' . $item->borneMin)
                                    ->where($this->intervallaireFieldBorneMax . ' <= ' . $item->borneMax);

        $qb2 = (new QueryBuilder())->update($this->getTable())
                                    ->set($this->intervallaireFieldBorneMin, $this->intervallaireFieldBorneMin . ' - ' . ($item->borneMax - $item->borneMin + 1),true)
                                    ->where($this->intervallaireFieldBorneMin . ' >= ' . $item->borneMax);

        $qb3 = (new QueryBuilder())->update($this->getTable())
                                    ->set($this->intervallaireFieldBorneMax, $this->intervallaireFieldBorneMax . ' - ' . ($item->borneMax - $item->borneMin + 1),true)
                                    ->where($this->intervallaireFieldBorneMax . ' > ' . $item->borneMax);

        $this->executeQb($qb1);
        $this->executeQb($qb2);
        $this->executeQb($qb3);

    }

	public function moveItem(Entity $item, $parent)
	{
		if($parent == null)
		{
			$parent = $this->getMasterParent();
		}
		elseif(is_int($parent))
		{
			$parent = $this->get($parent);
		}

		return $this->moveTo($item, $parent->borneMax, $parent->niveau + 1);
	}

	public function moveItemBefore(Entity $item, $itemBefore)
	{
		if(is_int($itemBefore))
		{
			$itemBefore = $this->get($itemBefore);
		}

		return $this->moveTo($item, $itemBefore->borneMin, $itemBefore->niveau);
	}

	public function moveItemAfter(Entity $item, $itemBefore)
	{
		if(is_int($itemBefore))
		{
			$itemBefore = $this->get($itemBefore);
		}

		return $this->moveTo($item, $itemBefore->borneMax+1, $itemBefore->niveau);
	}

	protected function moveTo(Entity $item, $newBorneMin, $newNiveau)
	{
		$qb1 = (new QueryBuilder())->update($this->getTable())
			->set($this->intervallaireFieldBorneMin, $this->intervallaireFieldBorneMin . ' + ' . ($item->borneMax - $item->borneMin + 1),true)
			->where($this->intervallaireFieldBorneMin . ' >= ' . $newBorneMin);

		$qb2 = (new QueryBuilder())->update($this->getTable())
			->set($this->intervallaireFieldBorneMax, $this->intervallaireFieldBorneMax . ' + ' . ($item->borneMax - $item->borneMin + 1),true)
			->where($this->intervallaireFieldBorneMax . ' >= ' . $newBorneMin);

		$decallage = ($item->borneMin >= $newBorneMin ? ($item->borneMax - $item->borneMin + 1) : 0);

		$qb3 = (new QueryBuilder())->update($this->getTable())
			->set($this->intervallaireFieldBorneMin, $newBorneMin . ' + ' . $this->intervallaireFieldBorneMin . ' - ' . ($item->borneMin + $decallage),true)
			->set($this->intervallaireFieldBorneMax, $newBorneMin . ' + ' . $this->intervallaireFieldBorneMax . ' - ' . ($item->borneMin + $decallage),true)
			->set($this->intervallaireFieldNiveau  , $this->intervallaireFieldNiveau . ' + ' . ($newNiveau - $item->niveau),true)
			->where($this->intervallaireFieldBorneMin . ' >= ' . ($item->borneMin + $decallage))
			->where($this->intervallaireFieldBorneMax . ' <= ' . ($item->borneMax + $decallage));


		$qb4 = (new QueryBuilder())->update($this->getTable())
			->set($this->intervallaireFieldBorneMin, $this->intervallaireFieldBorneMin . ' - ' . ($item->borneMax - $item->borneMin + 1),true)
			->where($this->intervallaireFieldBorneMin . ' > ' . $item->borneMax);

		$qb5 = (new QueryBuilder())->update($this->getTable())
			->set($this->intervallaireFieldBorneMax, $this->intervallaireFieldBorneMax . ' - ' . ($item->borneMax - $item->borneMin + 1),true)
			->where($this->intervallaireFieldBorneMax . ' > ' . $item->borneMax);

		$this->executeQb($qb1);
		$this->executeQb($qb2);
		$this->executeQb($qb3);
		$this->executeQb($qb4);
		$this->executeQb($qb5);

		return true;
	}

	public function getItemWithChilds($item = null)
	{
		$qb = $this->findWithRel()
					->order($this->intervallaireFieldBorneMin);

		if($item != null)
		{
			if(is_int($item))
			{
				$item = $this->get($item);
			}
			$qb->where($this->intervallaireFieldBorneMin . ' >= ' . $item->borneMin)
				->where($this->intervallaireFieldBorneMax . ' <= ' . $item->borneMax);
		}

		if($this->callable !== null)
		{
			call_user_func($this->callable, $qb);
		}

		return $qb;

		$ret = [];
		$pointers = [&$ret];

		foreach($qb as $v)
		{
			$ret[] = $v;
		}

		return $ret;
	}

	public function getItemWithParents($item)
	{
		$qb = $this->findWithRel()
					->order($this->intervallaireFieldBorneMin);

		if(is_int($item))
		{
			$item = $this->get($item);
		}

		$qb->where($this->intervallaireFieldBorneMin . ' <= ' . $item->borneMin)
			->where($this->intervallaireFieldBorneMax . ' >= ' . $item->borneMax);

		if($this->callable !== null)
		{
			call_user_func($this->callable, $qb);
		}

		return $qb;
	}
}