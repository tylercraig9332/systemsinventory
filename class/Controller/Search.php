<?php

namespace systemsinventory\Controller;

use systemsinventory\Factory\Search as Factory;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Ted Eberhard <eberhardtm at appstate dot edu>
 */
class Search extends \Http\Controller
{

    public $search_params = NULL;
    
    public function get(\Request $request)
    {
        $data = array();
        $view = $this->getView($data, $request);
        $response = new \Response($view);
        return $response;
    }

    protected function getHtmlView($data, \Request $request)
    {
        $content = Factory::form($request);
        $view = new \View\HtmlView($content);
        return $view;
    }

    protected function getJsonView($data, \Request $request){
        $db = \Database::newDB();
      $sd = $db->addTable('systems_device');
      if(!empty($_SESSION['system_search_vars']))
      $search_vars = $_SESSION['system_search_vars'];
      $conditional = NULL;
      
      if($search_vars['system_type']){
          $conditional = new \Database\Conditional($db, 'device_type_id', $search_vars['system_type'], '=');
      }
      if($search_vars['department']){
          $tmp_cond = new \Database\Conditional($db, 'department_id', $search_vars['department'], '=');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($search_vars['physical_id'])){
          $tmp_cond = new \Database\Conditional($db, 'physical_id', $search_vars['physical_id'], 'like');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($search_vars['model'])){
          $tmp_cond = new \Database\Conditional($db, 'model', "%".$search_vars['model']."%", 'like');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($search_vars['username'])){
          $tmp_cond = new \Database\Conditional($db, 'username', "%".$search_vars['username']."%", 'LIKE');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($search_vars['purchase_date'])){
          $from_date = strtotime($search_vars['purchase_date']);
          $to_date = strtotime($search_vars['purchase_date'])+86400;
          $tmp_cond = new \Database\Conditional($db, 'purchase_date', $from_date, '>');
          $tmp_cond1 = new \Database\Conditional($db, 'purchase_date', $to_date, '<');
          $tmp_cond = new \Database\Conditional($db, $tmp_cond, $tmp_cond1, 'AND');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($search_vars['ip'])){
           $tmp_cond = new \Database\Conditional($db, 'primary_ip', "%".$search_vars['ip']."%", 'like');
           $tmp_cond1 = new \Database\Conditional($db, 'secondary_ip', "%".$search_vars['ip']."%", 'like');
           $tmp_cond = new \Database\Conditional($db, $tmp_cond, $tmp_cond1, 'OR');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($search_vars['mac'])){
           $tmp_cond = new \Database\Conditional($db, 'mac', "%".$search_vars['mac']."%", 'like');
           $tmp_cond1 = new \Database\Conditional($db, 'mac2', "%".$search_vars['mac']."%", 'like');
           $tmp_cond = new \Database\Conditional($db, $tmp_cond, $tmp_cond1, 'OR');
          if(empty($conditional))
              $conditional = $tmp_cond;
          else
              $conditional = new \Database\Conditional ($db, $conditional, $tmp_cond, 'AND');
      }
      if(!empty($conditional))
          $db->addConditional ($conditional);
      $dbpager = new \DatabasePager($db);
      $dbpager->setHeaders(array('physical_id'=>'Physical ID', 'department_id'=>'Department','location_id'=>'Location','model'=>'Model','room_number'=>'Room Number', 'username'=>'Username','purchase_date'=>'Purchase Date'));
      $tbl_headers['physical_id'] = $sd->getField('physical_id');
      $tbl_headers['department_id'] = $sd->getField('department_id');
      $tbl_headers['location_id'] = $sd->getField('location_id');
      $tbl_headers['model'] = $sd->getField('model');
      $tbl_headers['room_number'] = $sd->getField('room_number');
      $tbl_headers['username'] = $sd->getField('username');
      $tbl_headers['purchase_date'] = $sd->getField('purchase_date');
      $dbpager->setTableHeaders($tbl_headers);
      $dbpager->setId('device-list');
      $dbpager->setRowIdColumn('id');
      $data = $dbpager->getJson();
      return parent::getJsonView($data, $request);
    }

    public function post(\Request $request){
      $factory = new Factory;
      $search_vars = $request->getVars();
      $_SESSION['system_search_vars'] = $search_vars['vars'];
      \Pager::prepare();
      $template = new \Template;
      $template->setModuleTemplate('systemsinventory', 'search_results.html');

      $view = new \View\HtmlView($template->get());
      $response = new \Response($view);
      return $response;
    }
    
}
