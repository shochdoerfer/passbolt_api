<?php
/**
 * Gpgkey Task
 *
 * @copyright    copyright 2012 Passbolt.com
 * @license      http://www.passbolt.com/license
 * @package      app.plugins.DataExtras.Console.Command.Task.GpgkeyTask
 * @since        version 2.12.11
 */

require_once(APP_DIR . DS  . 'Plugin' . DS . 'DataExtras' . DS . 'Console' . DS . 'Command' . DS . 'Task' . DS . 'ModelTask.php');

App::uses('User', 'Model');

class GpgkeyTask extends ModelTask {

	public $model = 'Gpgkey';

	public function execute() {
		$Model = ClassRegistry::init($this->model);
		$data = $this->getData();
		foreach ($data as $item) {
			$Model->create();
			$Model->set($item);
			if (!$Model->validates()) {
				var_dump($Model->validationErrors);
			}
			$instance = $Model->save();
			if (!$instance) {
				$this->out('<error>Unable to insert ' . $item[$this->model]['name'] . '</error>');
			}
		}
	}

	public function getUserKey($userId) {
		$key = file_get_contents(APP . DS . 'Config' . DS . 'gpg' . DS . 'passbolt_dummy_key.asc');
		return $key;
	}

	public function getData() {
		$User = Common::getModel('User');
		$us = $User->find('all');
		$Model = ClassRegistry::init($this->model);

		$k = array();

		foreach($us as $u) {
			$keyRaw = $this->getUserKey($u['User']['id']);
			$info = $Model->info($keyRaw);
			$key = array(
				'Gpgkey'=>array(
					'id' => Common::uuid(),
					'user_id' => $u['User']['id'],
					'key' => $keyRaw,
					'bits' => 0,
					'uid' => $info['uid'],
					'key_id' => $info['key_id'],
					'fingerprint' => $info['fingerprint'],
					'type' => $info['type'],
					'expires' => date('Y-m-d H:i:s', $info['expires']),
					'key_created' => date('Y-m-d H:i:s', $info['key_created']),
					'created' => date('Y-m-d H:i:s'),
					'modified' => date('Y-m-d H:i:s'),
					'created_by' => $u['User']['id'],
					'modified_by' => $u['User']['id'],
				)
			);
			$k[] = $key;
		}
		return $k;
	}
}