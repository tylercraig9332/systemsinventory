<?php

namespace systemsinventory\Factory;

use systemsinventory\Resource\Settings as Resource;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Ted Eberhard
 */
class Settings extends \ResourceFactory
{
    public static function userPermissionsView($data, $request){

        $departments = SystemDevice::getSystemDepartments();
        $dep_options = '';
 
        $vars['js'] = "<script type='text/javascript' src='".PHPWS_SOURCE_HTTP."mod/systemsinventory/javascript/bootstrap-multiselect.js'></script>";
        $vars['css'] = "<link rel='stylesheet' href='".PHPWS_SOURCE_HTTP."mod/systemsinventory/css/bootstrap-multiselect.css'/>";

        foreach($departments as $val){
            if($val['id'] != 1)
                $dep_options .= '<option value="'.$val['id'].'">'.$val['description'].'</option>';
        }
        $vars['departments'] = $dep_options;
        $users = Settings::getPHPWSUsers();
        $user_options = '';
        foreach($users as $user){
            $user_options .= '<option value="'.$user['id'].'">'.$user['username'].'</option>';
        }
        $vars['users'] = $user_options;
        
        \Pager::prepare();
        $template = new \Template;
        $template = new \Template($vars);
        $template->setModuleTemplate('systemsinventory', 'Edit_Settings.html');
        return $template->get();
    }
    
    public static function userPermissionsList($data, $request){
        $users = Settings::getPHPWSUsers();
        $departments_result = SystemDevice::getSystemDepartments();
        // convert to associative array
        foreach($departments_result as $dept){
            $departments[$dept['id']] = $dept['description'];
        }
        $rows = array();
        $db = \Database::getDB();
        $tbl = $db->addTable('systems_permission');
        $tbl->addField('user_id');
        $tbl->addField('departments');
        $tbl->addField('id');
        foreach($users as $user){
            $permissions = '';
            $permitted_dept = array();
            $db->clearConditional();
            $user_id = $user['id'];
            $tbl->addFieldConditional('user_id', $user_id, '=');
            $result = $db->select();
            if(!empty($result)){
                $permitted_dept = explode(':',$result['0']['departments']);
                foreach($permitted_dept as $dept){
                    if(!empty($permissions))
                        $permissions .= ', ';
                    $permissions .= $departments[$dept];
                }
            }else{
                $permissions = "All Departments!";
            }
            $action = '<a style="cursor:pointer" onclick="return confirm(\'Are you sure you want to delete this users permissions?\')" href="./systemsinventory/settings/editPermissions/?action=delete&user_id='.$user_id.'")>
                <span class="glyphicon glyphicon-trash" title="Delete Restrictions"></span></a>';
            $rows[] = array('display_name'=>$user['display_name'],'username'=>$user['username'],'permissions'=>$permissions,'action'=>$action);
        }
        $pager = new \Pager;
        $pager->setId('user-permissions-list');
        $pager->setHeaders(array('display_name'=>'Name','username'=>'Username','permissions'=>'Current Permissions'));
        $pager->setRows($rows);
        $data = $pager->getJson();
        return $data;
    }
  
  public function savePermissions(\Request $request){
      $vars = $request->getRequestVars();
      $users = $vars['users_multiselect'];
      $departments = implode(':',$vars['department_multiselect']);
      $db = \Database::getDB();
      $tbl = $db->addTable('systems_permission');
      $tbl->addField('user_id');
      $tbl->addField('id');
      foreach($users as $user){
          $db->clearConditional();
          $tbl->addFieldConditional('user_id', $user, '=');
          $result = $db->select();
          $resource = new Resource;
          $resource->setDepartments($departments);
          $resource->setUserID($user);
          if(!empty($result)){
              $id = $result['0']['id'];
              $resource->setId($id);
          }
          $resource->save();
      }   
      
  }
  
  public static function deletePermissions($user_id){
          $db = \Database::getDB();
          $tbl = $db->addTable('systems_permission');
          $tbl->addFieldConditional('user_id', $user_id,'=');
          return $db->delete();
          
      }
  
  private static function getPHPWSUsers(){
      $db = \Database::getDB();
      $tbl = $db->addTable('users');
      $tbl->addField('id');
      $tbl->addField('username');
      $tbl->addField('display_name');
      $tbl->addFieldConditional('deity', 0, '=');
      $result = $db->select();
      if(!empty($result))
          return $result;
      else 
          return FALSE;
  }  

}
