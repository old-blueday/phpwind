<?php
!defined('P_W') && exit('Forbidden');

/**
 * memcached缓存插件，需服务器环境支持
 * 
 * @package Cache
 */
class PW_Memcache {
	var $cache = null;
	var $connected = null;
	var $config = array();

	/**
	 * 根据默认配置自动连接Memcahce服务器,当参数为TRUE时
	 *
	 * @param bool $connect
	 * @return PW_Memcache
	 */
	function PW_Memcache($connect = true) {
		if ($this->exists()) {
			$this->cache = new Memcache;
			if ($connect) {
				$this->config = $GLOBALS['db_memcache'] ? $GLOBALS['db_memcache'] : array('host'=>'localhost','port'=>11211);
				$this->connect();
			}
		}
	}

	function addServer($host,$port) {
		$this->config[] = array('host'=>$host,'port'=>$port);
	}

	/**
	 * 连接Memcache服务器
	 *
	 */
	function connect ($force = false) {
		if ($force && $this->isConnected()) {
			$this->close();
		}
		if (is_null($this->connected)) {
			$this->connected = true;
			if (isset($this->config[0])) {
				if (method_exists($this->cache, 'addServer')) {
					foreach ($this->config as $value) {
						$this->cache->addServer($value['host'],$value['port']);
					}
				} elseif (!$this->cache->connect($this->config[0]['host'],$this->config[0]['port'])) {
					$this->connected = false;
				}
			} elseif (!$this->cache->connect($this->config['host'],$this->config['port'])) {
				$this->connected = false;
			}
		}
	}

	function close() {
		if ($this->isConnected()) {
			$this->cache->close();
			$this->connected = null;
		}
	}

	/**
	 * 清空memcache缓存
	 *
	 * @return bool
	 */
	function flush() {
		if (!$this->isConnected()) return false;
		return $this->cache->flush();
	}

	/**
	 * 删除指定KEY的数据
	 *
	 * @param string $key
	 * @return bool
	 */
	function delete($key) {
		if (!$this->isConnected()) return false;
		if(is_array($key)){
			foreach($key as $k){
				$k = $this->_getKeyPrefix($k);
				$this->cache->delete($k);
			}
		}else{
			$key = $this->_getKeyPrefix($key);
			$this->cache->delete($key);
		}
		return true;
	}

	/**
	 * 批量更新memcache缓存数据
	 *
	 * @param array $data 缓存数据,array('KEY'=>'VALUE')
	 * @param int $expire 缓存数据自动过期时间(秒)
	 * @return bool
	 */
	function update($data,$expire=86400) {
		if (!$this->isConnected()) return false;
		foreach ($data as $key=>$value) {
			$this->set($key,$value,$expire);
		}
		return true;
	}

	/**
	 * 更新指定KEY的缓存数据
	 *
	 * @param string $key 缓存KEY
	 * @param string $value
	 * @param int $expire
	 * @return bool
	 */
	function set($key,$value,$expire=86400) {
		if (!$this->isConnected()) return false;
		$key = $this->_getKeyPrefix($key);
		return $this->cache->set($key,$value,MEMCACHE_COMPRESSED,$expire);
	}

	/**
	 * 获取指定KEY的数据
	 *
	 * @param string|array $keys
	 * @return string|array
	 */
	function get($keys) {
		if (!$this->isConnected()) return false;
		if (is_array($keys)) {
			$data = array();
			foreach ($keys as $key) {
				$result = $this->_get($key);
				if($result){
					$data[$key] = $result;
				}
			}
			return $data;
		} else {
			return $this->_get($keys);
		}
	}

	/**
	 * 获取Memcache实例化对象
	 *
	 * @return object
	 */
	function &getMemcache() {
		if (!is_object($this->cache)) {
			$this->cache = new Memcache;
		}
		return $this->cache;
	}

	function isConnected() {
		return $this->connected === true ? true : false;
	}

	/**
	 * 检查环境是否支持memcache组件
	 *
	 * @return bool
	 */
	function exists() {
		if (class_exists('Memcache')) {
			return true;
		}
		return false;
	}
	function _getKeyPrefix($key){
		if(is_array($key)){
			$keys = array();
			foreach($key as $t_key){
				$keys[] = $this->__getKeyPrefix($t_key);
			}
			return $keys;
		}
		return $this->__getKeyPrefix($key);
	}
	
	function __getKeyPrefix($key){
		static $_prefix=null;
		if (!$_prefix) {
			$_prefix = substr(md5($GLOBALS['db_hash']),18,5);
		}
		return $_prefix.'_'.$key;
	}

	function _get($key) {
		if (!$this->isConnected()) return false;
		$key = $this->_getKeyPrefix($key);
		return $this->cache->get($key);
	}
}
?>