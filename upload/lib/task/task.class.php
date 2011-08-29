<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 任务系统调度中心
 * @2009-11-23
 * 
 * @package Task
 */
class PW_Task {
	var $_db = null;
	var $_tasks = array();
	var $_interval = 6; /*每6小时通过一次*/
	var $_runInterval = 12; /*任务运行间隔每12小时*/
	var $_filename = 'tasks.php';
	var $_cache = false;
	var $_openVerity = true; /*是否开启时间限制*/
	var $_timestamp = null;
	var $_hour = 3600;
	function PW_Task() {
		global $db, $timestamp;
		$this->_db = & $db;
		$this->_timestamp = $timestamp;
	}
	/**
	 * 运行任务
	 */
	function run() {
		if ($this->verify()) {
			$this->doTask();
		}
		return true;
	}
	/**
	 * 执行任务
	 */
	function doTask() {
		$tasks = $this->gets();
		if (!$tasks) {
			return null;
		}
		foreach($tasks as $task) {
			$this->tasklist($task['task']);
		}
	}
	/**
	 * 任务列表
	 */
	function tasklist($task) {
		switch ($task) {
			case 'alteradver':
				$this->alterAdver();
				break;

			default:
				break;
		}
		return true;;
	}
	/**
	 * 任务一：广告到期提醒任务
	 */
	function alterAdver() {
		list($id, $name, $task, $next) = array_values($this->getDefaultTask('alteradver'));
		$class = $this->_loadTask($task);
		$finish = $class->run();
		if ($finish) {
			$this->update($id, $name, $task, $next);
		}
		return true;
	}
	/**
	 * 单任务检查
	 */
	function check($id, $name, $task, $next) {
		$tasks = $this->get($id);
		if (!$tasks) {
			$next = $this->_timestamp + $next;
			$this->add($id, $name, $task, $next);
			return true;
		}
		if ($tasks['next'] <= $this->_timestamp) {
			return true;
		}
		return false;
	}
	/**
	 * 系统任务默认设置
	 */
	function getDefaultTask($k) {
		$current = $this->_timestamp;
		$next = $current + $this->_interval * $this->_hour; /*默认设置间隔时间*/
		$tasks = array(
			'alteradver' => array(
				'id' => 1,
				'name' => '广告到期提醒',
				'task' => 'alteradver',
				'next' => $next
			)
		);
		return $tasks[$k];
	}
	/**
	 * 增加一条任务记录
	 */
	function add($id, $name, $task, $next) {
		$this->_db->update("INSERT INTO pw_task" . " SET " . S::sqlSingle(array(
			'id' => ($id) ? $id : '',
			'name' => $name,
			'task' => $task,
			'count' => 1,
			'last' => $this->_timestamp,
			'next' => $next,
			'ctime' => $this->_timestamp,
		)));
	}
	/**
	 * 更新一条任务记录
	 */
	function update($id, $name, $task, $next) {
		$this->_db->update("UPDATE pw_task SET count=count+1," . S::sqlSingle(array(
			'name' => $name,
			'task' => $task,
			'last' => $this->_timestamp,
			'next' => $next,
		)) . " WHERE id=" . S::sqlEscape($id));
	}
	/**
	 * 获取需要执行的任务列表
	 */
	function gets($current = null) {
		$current = $current ? $current : $this->_timestamp;
		$current = intval($current);
		if ($current < 1) {
			return array();
		}
		$result = array();
		$query = $this->_db->query("SELECT * FROM pw_task WHERE next<=" . $current);
		while ($rs = $this->_db->fetch_array($query)) {
			$result[$rs['id']] = $rs;
		}
		return $result;
	}
	/**
	 * 获取单条任务信息
	 */
	function get($id) {
		$id = intval($id);
		if ($id < 1) {
			return array();
		}
		return $this->_db->get_one("SELECT * FROM pw_task WHERE id=" . $id);
	}
	/*验证任务是否可以开始*/
	function verify() {
		if (!$this->_openVerity) {
			return true;
		}
		$current = $this->_timestamp;
		$configs = $this->getFileCache();
		if ($configs['next'] <= $current) {
			$this->setFileCache();
			return true;
		}
		return false;
	}
	/*获取任务配置*/
	function taskConfig() {
		$current = $this->_timestamp;
		$interval = $this->_runInterval;
		$last = $current;
		$next = $current + $interval * $this->_hour;
		return array(
			'interval' => $interval,
			'last' => $last,
			'next' => $next
		);
	}
	/**
	 * 设置文件缓存
	 */
	function setFileCache() {
		$configs = $this->taskConfig();
		$tasks = "\$tasks=" . pw_var_export($configs) . ";";
		pwCache::setData($this->getCacheFileName(), "<?php\r\n" . $tasks . "\r\n?>");
		return $configs;
	}
	/**
	 * 获取文件缓存
	 */
	function getFileCache() {
		if (!$this->_cache) {
			return $this->taskConfig(); /*not open cache*/
		}
		@include S::escapePath($this->getCacheFileName());
		if ($tasks) {
			return $tasks;
		}
		return $this->setFileCache();
	}
	/*获取缓存文件路径*/
	function getCacheFileName() {
		return D_P . "data/bbscache/" . $this->_filename;
	}
	/**
	 * 获取任务文件类
	 */
	function _loadTask($name) {
		static $classes = array();
		$name = strtolower($name);
		$filename = R_P . "lib/task/task/" . $name . ".task.php";
		if (!is_file($filename)) {
			return null;
		}
		$class = 'Task_' . ucfirst($name);
		if (isset($classes[$class])) {
			return $classes[$class];
		}
		include S::escapePath($filename);
		$classes[$class] = new $class();
		return $classes[$class];
	}
}
