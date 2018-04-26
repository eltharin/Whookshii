<?php
namespace core\classes;

class includes
{
	static function datatable()
	{
		\config::add_css('/css/datatable/filter.datatable.css');
		\config::add_css('/css/datatable/datatable.css');
		\config::add_css('/css/datatable/pager.datatable.css');
		\config::add_css('/css/datatable/menuLength.datatable.css');
		\config::add_css('/css/datatable/output.datatable.css');
		\config::add_css('/css/multiple-select.css');

		\config::add_script('/js/datatable/core/jquery.dataTables.js'); 
		\config::add_script('/js/datatable/renderers.datatable.js');
		\config::add_script('/js/datatable/dataTable.basic.js'); 
		
		\config::add_script('/js/datatable/widget/widget-pager.datatable.js'); 
		\config::add_script('/js/datatable/widget/widget-menuLength.datatable.js'); 
		\config::add_script('/js/datatable/widget/widget-json.datatable.js'); 
		\config::add_script('/js/datatable/widget/widget-filter.datatable.js'); 
		\config::add_script('/js/datatable/widget/widget-totalizer.datatable.js');
		\config::add_script('/js/datatable/widget/widget-numColFooter.datatable.js');
		\config::add_script('/js/datatable/widget/widget-output.datatable.js'); 
		\config::add_script('/js/multiple-select.js');
	}
}