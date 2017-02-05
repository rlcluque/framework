<?php

	if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();

	final class Message {
		
		private $messages = array();
		
		public function __construct ($str='') {
			
			if ($str != '') $this->buildArray($str);
			
		}
		
		public function __get ($name) {
			
			if (isset($this->messages[$name])) return $this->messages[$name];
			
		}
		
		public function is_set ($name) {
			
			if (isset($this->messages[$name])) return true;
			else return false;
			
		}
		
		public function add($tittle,$description) {
			
			$this->messages[$tittle] = $description;
			
		}
		
		public function toString() {
			
			$aux = $this->buildString();
			
			return $aux;
			
		}
		
		public function toJSON() {
			
			$aux = $this->messages;
			
			return json_encode($aux);
			
		}
		
		public function isEmpty() {
			
			if (count($this->messages) > 0) return false;
			else return true;
			
		}
		
		public function sendMessage() {
			
			echo $this->toJSON();
			
		}
		
		private function buildMessage ($str) {
			
			$this->__construct ($str);
			
		}
		
		private function buildArray ($str) {
			
			$messages = explode(';',$str);
			
			foreach ($messages as $message) {
				$aux = explode(':',$message);
				$tittle = $aux[0];
				$description = $aux[1];
				$this->messages[$tittle] = $description;
			}
			
		}
		
		private function buildString () {
			
			if (!isset($this->messages)) return false;
			
			$aAux = array();
			$sAux = '';
			$keys = array_keys($this->messages);
			foreach ($keys as $key) {
				$aAux[] = $key . ":" . $this->messages[$key];
			}
			$sAux = join(";",$aAux);
			
			return $sAux;
			
		}
		
	}