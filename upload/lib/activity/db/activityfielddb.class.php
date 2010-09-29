<?php
!defined('P_W') && exit('Forbidden');
/**
 * 活动主分类
 * 
 * @package activity
 */

class PW_ActivityFieldDB extends BaseDB {
	var $_tableName = 'pw_activityfield';
	var $_primaryKey = 'fieldid';
	function getFieldsByModelId ($modelId) {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' WHERE actmid=' . $this->_addSlashes($modelId) . ' ORDER BY ifdel ASC, vieworder ASC, ' . $this->_primaryKey . ' ASC');
		$fields = $this->_getAllResultFromQuery($query, $this->_primaryKey);
		foreach ($fields as $key=>$field) {
			$fields[$key] = $this->_getParsedField($field);
		}
		return $fields;
	}
	
	function getDefaultSearchFields() {
		$searchFieldDb = array (
			array (
				'fieldname' => 'starttime',
				'name' => 
					array (
						'活动时间',
						NULL,
						NULL,
					),
				'type' => 'calendar',
				'rules' => 
					array (
						'precision' => 'minute',
					),
				'ifsearch' => '1',
				'ifasearch' => '1',
				'vieworder' => '1',
				'textwidth' => '18',
			),
			array (
				'fieldname' => 'endtime',
				'name' => 
					array (
						'-',
						NULL,
						NULL,
					),
				'type' => 'calendar',
				'rules' => 
					array (
						'precision' => 'minute',
					),
				'ifsearch' => '1',
				'ifasearch' => '1',
				'vieworder' => '1',
				'textwidth' => '18',
			),
			array (
				'fieldname' => 'location',
				'name' => 
					array (
						'活动地点',
						NULL,
						NULL,
					),
				'type' => 'text',
				'rules' => '',
				'ifsearch' => '1',
				'ifasearch' => '1',
				'vieworder' => '2',
				'textwidth' => '40',
			),
			array (
				'fieldname' => 'contact',
				'name' => 
					array (
						'联系人',
						NULL,
						NULL,
					),
				'type' => 'text',
				'rules' => '',
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '4',
				'textwidth' => '20',
			),
			array (
				'fieldname' => 'telephone',
				'name' => 
					array (
						'联系电话',
						NULL,
						NULL,
					),
				'type' => 'text',
				'rules' => '',
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '5',
				'textwidth' => '18',
			),
			array (
				'fieldname' => 'signupstarttime',
				'name' => 
					array (
						'报名时间',
						NULL,
						NULL,
					),
				'type' => 'calendar',
				'rules' => 
					array (
						'precision' => 'minute',
					),
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '6',
				'textwidth' => '18',
			),
			array (
				'fieldname' => 'signupendtime',
				'name' => 
					array (
						'-',
						NULL,
						NULL,
					),
				'type' => 'calendar',
				'rules' => 
					array (
						'precision' => 'minute',
					),
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '6',
				'textwidth' => '18',
			),
			array (
				'fieldname' => 'userlimit',
				'name' => 
					array (
						'报名限制',
						NULL,
						NULL,
					),
				'type' => 'radio',
				'rules' => 
					array (
						0 => '1=所有用户',
						1 => '2=仅好友',
					),
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '8',
			),
			array (
				'fieldname' => 'specificuserlimit',
				'name' => 
					array (
						'请输入其它限制',
						NULL,
						NULL,
					),
				'type' => 'text',
				'rules' => '',
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '8',
				'textwidth' => '14',
			),
			array (
				'fieldname' => 'genderlimit',
				'name' => 
					array (
						'性别限制',
						NULL,
						NULL,
					),
				'type' => 'radio',
				'rules' => 
					array (
						0 => '1=全部',
						1 => '2=仅男生',
						2 => '3=仅女生',
					),
				'ifsearch' => '0',
				'ifasearch' => '1',
				'vieworder' => '9',
			),
			array (
				'fieldname' => 'paymethod',
				'name' => 
					array (
						'支付方式',
						NULL,
						NULL,
					),
				'type' => 'radio',
				'rules' => 
					array (
						0 => '1=支付宝',
						1 => '2=现金支付',
					),
				'ifsearch' => '1',
				'ifasearch' => '1',
				'vieworder' => '12',
			)
		);
		return $searchFieldDb;
	}
	
	/**
	 * 返回处理过的字段
	 * 
	 * 将rule序列化，name分解成3部分
	 * @param array $field
	 * @return array 处理过的字段
	 * @access private
	 */
	function _getParsedField($field) {
		if (!is_array($field)) {
			return false;
		}
		if ($field['rules']) {
			$field['rules'] = $this->_unserialize($field['rules']);
		}
		$field['nameInDb'] = $field['name'];
		$field['name'] = $this->_getNamePartsByName($field['name']);
		return $field;
	}
	
	/**
	 * 返回3部分名字
	 * @param string $name 原名字，格式：部分1{#}部分2{@}部分3
	 * @return array 3部分名字
	 * @access private
	 */
	function _getNamePartsByName($name) {
		list($name1, $name3) = explode('{#}',$name);
		list($name1, $name2) = explode('{@}',$name1);
		return array($name1, $name2, $name3);
	}
	
	function getField($id) {
		$field = $this->_get($id);
		return $this->_getParsedField($field);
	}
	
	function update($id, $fieldData) {
		$this->_update($fieldData,$id);
	}
	
	function getFieldsByIds($ids) {
		$query = $this->_db->query('SELECT * FROM ' . $this->_tableName . ' WHERE ' . $this->_primaryKey . ' IN (' . $this->_getImplodeString($ids) . ')');
		$fields = $this->_getAllResultFromQuery($query, $this->_primaryKey);
		foreach ($fields as $key=>$field) {
			$fields[$key] = $this->_getParsedField($field);
		}
		return $fields;
	}
	
	function insert($fieldData) {
		return $this->_insert($fieldData);
	}
	
	function delete($id) {
		return $this->_delete($id);
	}
	
	function getFieldByModelIdAndName($modelId, $fieldName) {
		$field = $this->_db->get_one('SELECT * FROM ' . $this->_tableName . ' WHERE actmid=' . $this->_addSlashes($modelId) . ' AND fieldname=' . $this->_addSlashes($fieldName));
		return $this->_getParsedField($field);
	}
}