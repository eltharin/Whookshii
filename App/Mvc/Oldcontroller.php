<?php
namespace Core\App\Mvc;

use Psr\Http\Message\ServerRequestInterface;

class Oldcontroller
{
	public $class;
	public $vars;
	protected $request;


	final function __construct(ServerRequestInterface $request)
	{
		$this->request = $request;
		$this->vars = array();
		$this->class = new \StdClass();

		$this->class->namespace = explode('\\',get_class($this));

		//$this->first_folder = strtolower(array_shift($this->class->name));
		if(strtolower($this->class->namespace[0]) == 'plugin')
		{

			$this->class->folderId = 2;
			//$this->class->name[1] = $this->class->name[0];
		}
		else
		{
			$this->class->folderId = 1;
		}

		if(strtolower($this->class->namespace[$this->class->folderId]) !== 'controllers')
		{
			throw new \Exception('Oldcontroller invalide');
		}


		//$this->class->namespace = (substr($this->class->namespace,0,5) == 'core\\'?substr($this->class->namespace,5):$this->class->namespace);

		$name = $this->class->namespace;
		unset($name[$this->class->folderId]);
		unset($name[0]);
		$this->class->name = $name;

		$model = $this->class->namespace;
		$model[$this->class->folderId] = 'Models';

		$view = $this->class->namespace;
		$view[$this->class->folderId] = 'Views';

		$this->class->model = implode('\\',$model);
		$this->class->modelname = lcfirst(implode('_',$this->class->name));

		$this->viewfolder = implode(DS,$view) . DS;

		$this->url = '/' . /*core::$request->get_url_base() .*/ implode('_',$this->class->name);
		
		/*
		$this->data = new stdClass();

*/

		$this->_init();

		$this->LoadModel(implode('\\',$this->class->name),false);
	}

	function __get($name)
    {
        if(isset($this->{lcfirst($name)}))
        {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[0];
            $origine = 'File : ' . $backtrace['file'] . '<br>Line : ' . $backtrace['line'] . '<br>Nom de l\'attribut appelé : $this->' . $name;
            $mail = new \Plugin\Intranet\classes\mailer();
            $mail->setFrom('Core@intranet.fr');
            $mail->addAddress('dev@cogep.fr');
            $mail->Subject = 'get utilisé sur Controlleur';
            $mail->msgHTML($origine);
            $mail->send();
        }
    }

    function init()	{}

	function _init()
	{
		$this->init();

	}
	
	function LoadController($name)
	{
		$ctrl = \Core\App\Loader::Load('Controllers',$name);
		$ctrl = $ctrl['name'];
		
		return new $ctrl();
	}
	
	function LoadModel($array,$show_error=true)
	{
		//-- deprecated function do not use

		if (!is_array($array))
		{
			$array = array($array);
		}

		foreach($array as $name)
		{
			if($model = \Core\App\Loader::Load('Models',$name))
			{
				$modelname = (str_replace(['\\','/'],['_','_'],$model['finalname']));
				$model = $model['name'];

                $this->{lcfirst($modelname)} = new $model();
                if(count($array) == 1)
				{
					return $this->{lcfirst($modelname)};
				}
				//$this->$modelname = &$this->{lcfirst($modelname)};
			}
			elseif ($show_error == true)
			{
				if ($_SESSION['debug_mode'] == 1)
				{
					\HTTP::errorPage('500','Le model ' . $name . ' n\'existe pas.');
				}
				else
				{
					\HTTP::errorPage('404');
				}
			}
		}
	}

	function __allow($action,$profil)
	{
		\ACL::allow($this->class->modelname,strtolower($action),$profil);
	}

	function __deny($action,$profil)
	{
		\ACL::deny($this->class->modelname,strtolower($action),$profil);
	}

	function __is_allowed($action)
	{
		return \ACL::have_right($this->class->modelname,'allow',$action);
	}

	function __is_denied($action)
	{
		return \ACL::have_right($this->class->modelname,'deny',$action);
	}



	function __can($action)
	{
		$action = strtolower($action);
		return \Core\App\ACL::have_right($this->class->model,$action);
	}

	public function Action_index(){}

	public function launch_function(String $action,array $params = array())
	{
		$methods = get_class_methods($this);
		$actiontolaunch = 'Action_'.$action;

		if (in_array($actiontolaunch,$methods))
		{
			if ($this->__can($action))
			{
				call_user_func_array(array($this,$actiontolaunch), $params);
			}
			else
			{
				\HTTP::errorPage('403','Vous n\'êtes pas authorisé à executer cette fonction.');
			}
		}
		else
		{
			\HTTP::errorPage('500','fonction ' . $action . ' non trouvée');
		}
	}

	protected function render($vue,$controller=null)
	{
		if ($controller !== null)
		{
			$ctrl = $this->LoadController($controller);
			$ctrl->add_vars($this->vars);
			$ctrl->render($vue);
		}
		else
		{
			$this->vue = ROOT . $this->viewfolder .$vue . '.php';
			
			if(file_exists($this->vue))
			{
				unset($vue);
				unset($controller);
				extract($this->vars);
				require $this->vue;
			}
			else
			{
				\HTTP::errorPage('404','Cette page n\'existe pas : ' . DS . $this->viewfolder .$vue . '.php  '/* . $view*/);
			}
		}
	}

	function get_link($link='')
	{
		return $this->getLink($link);
	}
	
	function getLink($link='')
	{
		return $this->url . '/' . $link;
	}

	function add_vars($k,$v=null)
	{
		$this->addVars($k,$v);
	}
	
	function addVars($k,$v=null)
	{
		if(is_array($k))
		{
			$this->vars = array_merge($this->vars,$k);
		}
		else
		{
			$this->vars[$k] = $v;
		}
	}
	
	function add()
	{
		$this->add_vars($this->class->name, $this->{$this->class->modelname}->create_empty());
		$this->render('formulaire');
	}
	
	function update($param)
	{
		if ($param !== null)
		{
			$this->add_vars($this->class->name, $this->{$this->class->modelname}->get($param));
			$this->render('formulaire');
		}	
	}
	
	function save($val = null)
	{
		if($this->{$this->class->modelname} !== null)
		{
			if ($val === null)
			{
				$val = $_POST;
			}
			$this->{$this->class->modelname}->set_values($val);
			$this->{$this->class->modelname}->save();
		}
	}
	
	/*function delete($id,$param=array())
	{
		$param = array_merge(
				array(	'formid'=>'form_delete',
						'action'=>$_SERVER['PATH_INFO'],
						'valuetrue' => 'Oui',
						'valuefalse' => 'Non',
						'model' => $this->class->modelname
				),$param);
		
		if(/*isset($_POST['__csrf']) && *//*isset($_POST['confirm']) && ($_POST['confirm'] == $param['valuetrue']))
		{
			return $this->{$this->class->modelname}->delete($id);
		}
		else
		{		
			echo form::new_form(array('action'=>$param['action'],'id'=>$param['formid']));
			//echo form::hidden('__csrf',\CRSF::get());
			echo '<table class="noborder"><tr><td colspan=2>Voulez vous supprimer cet élément?</td></tr><tr><td>'.form::submit( $param['valuetrue'],0,'confirm').'</td><td>'.form::submit( $param['valuefalse'],0).'</td></tr></table>';
			
			echo form::endform();
		}
	}*/
}