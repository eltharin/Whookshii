<?php
namespace Core\App\Middleware;

class Xhprof extends MiddlewareAbstract
{
    static $model;

	public function beforeProcess()
	{
        self::enable();

	}

	public function afterProcess()
	{
        self::disable();
	}

    static function enable($flags = null, $ignored_functions = null)
    {
        if (extension_loaded('tideways_xhprof'))
        {
            if($flags === null)
            {
                $flags = TIDEWAYS_XHPROF_FLAGS_NO_BUILTINS;
            }
            tideways_xhprof_enable($flags/*, $ignored_functions*/);
        }
    }

    static function disable($saveType = 'database')
    {
        if (extension_loaded('tideways_xhprof'))
        {
            $data = self::getData();
            if ($saveType === 'database' && $data !== null)
            {
                self::loadModel();

                if (empty(self::$model->find('XHP_ACTION_CALLED = :action', ['action' => $data['action']])))
                {
                    self::save($data);
                }
            }
        }
    }

    private static function getData()
    {
        $xhprof_data = [];

        if (extension_loaded('tideways_xhprof'))
        {
            $xhprof_data = tideways_xhprof_disable();
        }

        if(substr($_SERVER['REQUEST_URI'],-4) != '.css' && substr($_SERVER['REQUEST_URI'],-3) != '.js' && substr($_SERVER['REQUEST_URI'],-4) != '.png'
            && substr($_SERVER['REQUEST_URI'],-4) != '.ico' && substr($_SERVER['REQUEST_URI'],-4) != '.gif')
        {

            $data['action'] = "/".\Config::$routes->get_controller()."/".\Config::$routes->get_action();

            $data['site'] = $_SERVER['SERVER_NAME'];

            foreach ($xhprof_data as $call => $stats)
            {
                $exploded_call = explode('==>', $call);
                $data[] = ['file'       => $exploded_call[0],
                           'method'     => isset($exploded_call[1])?$exploded_call[1]:'',
                           'calledNum'  => $stats['ct'],
                           'wallTime'   => $stats['wt']];
            }
        }
        if(!isset($data))
        {
            return null;
        }
        return $data;
    }

    private static function loadModel()
    {
        $model = \Core\App\Loader::load('Models','xhprof');
        self::$model = new $model['name']();
    }

    private static function save($data)
    {
        $dataSaved['XHP_ACTION_CALLED'] = $data['action'];
        $dataSaved['XHP_SITE'] = $data['site'];
        unset($data['action']);
        unset($data['site']);
        foreach ($data as $call)
        {
            if(!self::classesAlreadyChecked($call['file'], $call['method']))
            {
                $dataSaved['XHP_FILE']              = $call['file'];
                $dataSaved['XHP_CALL_WALL_TIME']    = $call['wallTime'];
                $dataSaved['XHP_CALL_NUMBER']       = $call['calledNum'];
                $dataSaved['XHP_METHOD']            = $call['method'];
                self::$model->save($dataSaved);
            }
        }
    }

    private static function classesAlreadyChecked($file, $method)
    {
        //$classes = ['Core', 'myloader', 'PHPExcel', 'SMTP', 'TCPDF', 'PHPMailer', 'xhprof', 'main()']; // a changer pour le nouveau core
        $classes = [];
        $boolean = false;
        foreach ($classes as $name) {
            if (strpos($file, $name) === 0 || strpos($method, $name) === 0) {
                $boolean = true;
            }
        }
        return $boolean;
    }
}