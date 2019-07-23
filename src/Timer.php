<?php

namespace Felideo;

class Timer {
	private $start_time   = 0;
	private $end_time     = 0;
	private $memory_start = 0;
	private $laps         = [];
	private $lapCount     = 0;
	private	$timeZone     = 'America/Sao_Paulo';
	private	$defaultBacktraceIndex = [
		'class'    => 1,
		'line'     => 0,
		'function' => 1,
		'file'     => 0,
	];

	private function __construct() {
		$this->reset();
	}

	public static get_timer(){
		if(isset($_SESSION['performance_test']) && is_object($_SESSION['performance_test']) && $_SESSION['performance_test'] instanceof felideo\Timer){
			return $_SESSION['performance_test'];
		}

		$_SESSION['performance_test'] = new felideo\Timer();

		return $_SESSION['performance_test'];
	}

	public function setTimeZone(String $timeZone) {
		$this->timeZone = $timeZone;
		return $this;
	}

	public function reset(){
		$this->startTime = 0;
		$this->endTime   = 0;
		$this->pauseTime = 0;
		$this->laps      = [];
		$this->lapCount  = 0;
	}

	public function start($name = "start"){
		$this->startTime = $this->getCurrentTime();
		$this->lap($name, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
	}

	public function lap($name = null){
		$this->endLap();

		if(empty($backtrace)){
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}

		$backtrace = $this->preparaBacktrace($backtrace);

		$this->laps[] = [
			"name"      => ($name ? $name : $this->lapCount),
			"start"     => $this->getCurrentTime(),
			"called_on" => [
				"Class/Function/Line" => "CLASS => " . $backtrace['class'] . " - FUNCTION => " . $backtrace['function'] . " - LINE => " . $backtrace['line'],
				"File"                => $backtrace['file'],
			],
			"end"       => -1,
			"total"     => -1,
		];

		$this->lapCount += 1;
	}

	public function endLap(){
		$lapCount = count($this->laps) - 1;
		if(count($this->laps) > 0){
			$this->laps[$lapCount]['end']   = $this->getCurrentTime();
			$this->laps[$lapCount]['total'] = $this->laps[$lapCount]['end'] - $this->laps[$lapCount]['start'];
		}
	}

	private function preparaBacktrace($backtrace) {
		return [
			'class'    => isset($backtrace[$this->defaultBacktraceIndex['class']]['class']) ? $backtrace[$this->defaultBacktraceIndex['class']]['class'] 		: '',
			'line'     => isset($backtrace[$this->defaultBacktraceIndex['line']]) 			? $backtrace[$this->defaultBacktraceIndex['line']]['line'] 			: '',
			'function' => isset($backtrace[$this->defaultBacktraceIndex['function']]) 		? $backtrace[$this->defaultBacktraceIndex['function']]['function'] 	: '',
			'file'     => isset($backtrace[$this->defaultBacktraceIndex['file']]) 			? $backtrace[$this->defaultBacktraceIndex['file']]['file'] 			: '',
		];
	}

	public function stop() {
		$this->endTime = $this->getCurrentTime();
		$this->endLap();
	}

	public function summary() {
		$this->removeStartsEnds();

		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$backtrace = $this->preparaBacktrace($backtrace);

		$return = [
			'running'   => $this->state,
			'start'     => $this->formatDate($this->startTime),
			'end'       => $this->formatDate($this->endTime),
			'total'     => $this->formatTime($this->endTime - $this->startTime),
			'paused'    => $this->formatTime($this->totalPauseTime),
			"called_on" => [
				"Class/Function/Line" => "CLASS => " . $backtrace['class'] . " - FUNCTION => " . $backtrace['function'] . " - LINE => " . $backtrace['line'],
				"File"                => $backtrace['file'],
			],
			'laps'      => $this->laps,
		];

		if (!empty($this->removeCalledOnInfo)) {
			unset($return['called_on']);
		}

		return $return;
	}

	public function removeStartsEnds() {
		foreach ($this->laps as $index => $lap) {
			unset($this->laps[$index]['start']);
			unset($this->laps[$index]['end']);
		}
	}

	public function getCurrentTime() {
		return microtime( true );
	}
}


