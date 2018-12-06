<?php
namespace Core\App\Mvc;

class Model_intervallaire extends Model
{
	protected function _BeforeInsert()	{	if(parent::_BeforeInsert()) {return $this->__BeforeInsert_intervallaire();} return false;}
	
	protected function __BeforeInsert_intervallaire()	
	{	
		//On prépare la mise en tampon
		$this->values[$this->valmin] = 0;
		$this->values[$this->valmax] = -1;	

		return true;
	}
	
	protected function _AfterInsert()	{	parent::_AfterInsert();	$this->__AfterInsert_intervallaire();}
	
	protected function __AfterInsert_intervallaire()
	{
		if ((isset($this->vars['idparent'])) && ($this->vars['idparent'] != 0))
		{
			$parent = $this->get($this->vars['idparent']);
			$valmax = $parent[$this->valmax];
		}
		else
		{
			$last = $this->db->select('*')->from($this->table)->order($this->valmax . ' DESC')->limit('1')->findfirst();
			$valmax = $last[$this->valmax]+1;
		}
		
		//On agrandit la maison
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' + 2 WHERE ' . $this->valmin . ' >= ' . $valmax);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $this->valmax . ' + 2 WHERE ' . $this->valmax . ' >= ' . $valmax);
		
		//On insère le petit nouveau
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $valmax . ', ' . $this->valmax . ' = ' . $valmax .' + 1 WHERE '.$this->primaryKey.' = ' . $this->values[$this->primaryKey] );
		//$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $valmax . ', ' . $this->valmax . ' = ' . $valmax .' + 1 WHERE SY_IT_ITEM = ' . $this->values['SY_IT_ITEM'] );
		
			
		return true;
	}
	
	protected function _BeforeDelete()	{	if(parent::_BeforeDelete()) {return $this->__BeforeDelete_intervallaire();} return false;}
	
	protected function __BeforeDelete_intervallaire()	
	{	
		$this->vars['item'] = $this->get($this->getpk());
		
		if(($this->vars['item'][$this->valmax] - $this->vars['item'][$this->valmin]) > 1)
		{
			//return false;
		}
		return true;
	}	
	
	protected function _AfterDelete()	{	parent::_AfterDelete();$this->__AfterDelete_intervallaire();}
	
	protected function __AfterDelete_intervallaire()	
	{	
		
		$this->db->query('DELETE FROM ' . $this->table . ' WHERE ' . $this->valmin . ' > ' . $this->vars['item'][$this->valmin] . ' AND ' . $this->valmax . ' < ' . $this->vars['item'][$this->valmax]);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' - ' . ($this->vars['item'][$this->valmax] - $this->vars['item'][$this->valmin] + 1) . ' WHERE ' . $this->valmin . ' >= ' . $this->vars['item'][$this->valmax]);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $this->valmax . ' - ' . ($this->vars['item'][$this->valmax] - $this->vars['item'][$this->valmin] + 1) . ' WHERE ' . $this->valmax . ' >= ' . $this->vars['item'][$this->valmax]);
		
		return true;
	}
	
	function change_parent($id_a_deplacer,$newparent)
	{	

		$item = $this->get($id_a_deplacer);
		//$allparents = $this->get_parents($id_a_deplacer);
		
		//if ($allparents[0])
		$nparent = $this->get($newparent);

		//$diffniv = $nparent[$this->niveau] - $item[$this->niveau] + 1;
			
		$nb_item = $this->db->select('count(SY_IT_ITEM) item')
									->from('item')
									->where('(' . $this->valmin . ' >= ' . $item[$this->valmin] . ' and ' . $this->valmax . ' <= ' . $item[$this->valmax].')')
									->findfirst();

		$bn_max = $item[$this->valmax];
		$bn_min = $item[$this->valmin];
		

		//$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->niveau . ' = ' . $diffniv . ' + ' . $this->niveau . ' WHERE ' . $this->valmin . ' >= ' . $item[$this->valmin] . ' and ' . $this->valmax . ' <= ' . $item[$this->valmax]);

		//On sort l'item
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' - ' . $bn_max . ', ' . $this->valmax . ' = ' . $this->valmax . ' - ' . $bn_max . ' WHERE ' . $this->valmin . ' >= ' . $bn_min .' and '.$this->valmax . ' <= ' . $bn_max);

		//On met à jour les autres bornes
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' - (' . $nb_item['item'] . ' * 2) WHERE ' . $this->valmin . ' > ' . $bn_min);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $this->valmax . ' - (' . $nb_item['item'] . ' * 2) WHERE ' . $this->valmax . ' > ' . $bn_max);

		//On pousse les bornes du parents et du frère
		$nparent = $this->get($newparent);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' + (' . $nb_item['item'] . ' * 2) WHERE ' . $this->valmin . ' >= ' . $nparent[$this->valmax]);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $this->valmax . ' + (' . $nb_item['item'] . ' * 2) WHERE ' . $this->valmax . ' >= ' . $nparent[$this->valmax]);
		
		//On intègre l'item dans l'arbre
		$nparent = $this->get($newparent);
	    $this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $nparent[$this->valmax] . ' - 1 + ' . $this->valmin . '  WHERE ' . $this->valmin . ' <= 0');
	    $this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $nparent[$this->valmax] . ' - 1 + ' . $this->valmax . '  WHERE ' . $this->valmax . ' <= 0');



		
		
		
		
		

		/*$size = $item[$this->valmax] - $item[$this->valmin];
		
		$diffniv = $nparent[$this->niveau] - $item[$this->niveau] + 1;
		$diffmin = $nparent[$this->valmax] - $item[$this->valmin];*/
		
		/*\HTML::print_r($parent);
		\HTML::print_r($item);*/
		//$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->niveau . ' = ' . $diffniv . ' + ' . $this->niveau . ' WHERE ' . $this->valmin . ' >= ' . $item[$this->valmin] . ' and ' . $this->valmax . ' <= ' . $item[$this->valmax]);
		



//-- on déplace l'élément
		/*$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $diffmin . ' + ' . $this->valmin . ' WHERE ' . $this->valmin . ' >= ' . $item[$this->valmin] . ' and ' . $this->valmin . ' <= ' . $item[$this->valmax]);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $diffmin . ' + ' . $this->valmax . ' WHERE ' . $this->valmax . ' >= ' . $item[$this->valmin] . ' and ' . $this->valmax . ' <= ' . $item[$this->valmax]);
				
		//-- on pousse les murs du nouvel emplacement
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' + ' . ($size+1) . ' WHERE ' . $this->valmin . ' >= ' . $nparent[$this->valmax]);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $this->valmax . ' + ' . ($size+1) . ' WHERE ' . $this->valmax . ' >= ' . $nparent[$this->valmax]);
		
		
		
		//-- on tasse l'ancien
		$this->db->query ('UPDATE ' . $this->table . ' SET ' . $this->valmin . ' = ' . $this->valmin . ' - ' . ($size+1) . ' WHERE ' . $this->valmin . ' > ' . $item[$this->valmax]);
		$this->db->query('UPDATE ' . $this->table . ' SET ' . $this->valmax . ' = ' . $this->valmax . ' - ' . ($size+1) . ' WHERE ' . $this->valmax . ' > ' . $item[$this->valmax]);*/
	
	}
	
	public function create_children($data)
	{
		$ret = array();
		$ret['children'] = array();

			if (count($data) > 0)
			{
				$myniveau = array();
				$niveau = 0;			
				$arbo = array();
				$arbo[$niveau] = &$ret;

				foreach ($data as $k => $elem)
				{
					if($k > 0 )
					{
						while($niveau && $elem[$this->valmin] > $myniveau[$niveau ]['max'])
						{
							unset($myniveau[$niveau]);
							$niveau--;
						}
						if($elem[$this->valmax] < $myniveau[$niveau ]['max'])
						{
							$niveau++;
						}
					}

					$myniveau[$niveau]['min'] = $elem[$this->valmin];
					$myniveau[$niveau]['max'] = $elem[$this->valmax];
					
				
					$arbo[$niveau]['children'][] = $elem;
					$arbo[$niveau + 1] = &$arbo[$niveau]['children'][count($arbo[$niveau]['children']) - 1];

					
					/*if ($k > 0)
					{
						if ($elem[$this->valmin] == ($data[$k - 1][$this->valmin] + 1))
						{
							$niveau ++;							
						}
						elseif ($elem[$this->valmin] != ($data[$k - 1][$this->valmax] + 1))
						{
							\html::print_r($elem[$this->valmin]);
							\html::print_r($data[$k - 1][$this->valmax] + 1);
							\html::print_r($elem[$this->valmin]  - $data[$k - 1][$this->valmax] - 1);
							$niveau -= ($elem[$this->valmin] - $data[$k - 1][$this->valmax] - 1);
						}
						
					}
					$arbo[$niveau]['children'][] = $elem;
					$arbo[$niveau + 1] = &$arbo[$niveau]['children'][count($arbo[$niveau]['children']) - 1];*/
				}
				
			}
			return $ret['children'];
	}
	
}