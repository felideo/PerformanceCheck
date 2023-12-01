<?php

namespace Felideo\Performance;

class Timer {
	private $start_time            = 0;
	private $end_time              = 0;
	private $laps                  = [];
	private $lap_count             = 0;
	private	$time_zone             = 'America/Sao_Paulo';
	private $xhprof                = null;
	private	$defaultBacktraceIndex = [
		'class'    => 1,
		'line'     => 0,
		'function' => 1,
		'file'     => 0,
	];

	private function __construct(){
		if(session_status() == PHP_SESSION_NONE){
			session_start();
		}

		$this->reset();
	}

	public static function get_timer(){
		if(isset($_SESSION['performance_check']) && is_object($_SESSION['performance_check']) && $_SESSION['performance_check'] instanceof felideo\Timer){
			return $_SESSION['performance_check'];
		}

		$_SESSION['performance_check'] = new Timer();

		return $_SESSION['performance_check'];
	}

	public function reset(){
		$this->start_time   = 0;
		$this->end_time     = 0;
		$this->laps         = [];
		$this->lap_count    = 0;
		return $this;
	}

	public function setTimeZone($time_zone){
		$this->time_zone = $time_zone;
		return $this;
	}

	public function defaultBacktraceIndex($class, $line, $function, $file){
		$this->defaultBacktraceIndex = [
			'class'    => $class,
			'line'     => $line,
			'function' => $function,
			'file'     => $file,
		];

		return $this;
	}

	public function start($name = "start"){
		$this->start_time = microtime(true);
		$this->lap($name, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
	}

	public function lap($name = null, $backtrace = null){
		if(empty($backtrace)){
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}

		$backtrace = $this->preparaBacktrace($backtrace);

		$this->laps[] = [
			"name"      => ($name ? $name : $this->lap_count),
			"start"     => microtime(true),
			"memory"    => memory_get_peak_usage(true) / 1048576 . ' Mb',
			"called_on" => [
				"Class/Function/Line" => "CLASS => " . $backtrace['class'] . " - FUNCTION => " . $backtrace['function'] . " - LINE => " . $backtrace['line'],
				"File"                => $backtrace['file'],
			],
			"end"       => -1,
			"total"     => -1,
		];

		$this->lap_count += 1;
	}

	private function preparaBacktrace($backtrace) {
		return [
			'class'    => isset($backtrace[$this->defaultBacktraceIndex['class']]['class']) ? $backtrace[$this->defaultBacktraceIndex['class']]['class'] 		: '',
			'line'     => isset($backtrace[$this->defaultBacktraceIndex['line']]) 			? $backtrace[$this->defaultBacktraceIndex['line']]['line'] 			: '',
			'function' => isset($backtrace[$this->defaultBacktraceIndex['function']]) 		? $backtrace[$this->defaultBacktraceIndex['function']]['function'] 	: '',
			'file'     => isset($backtrace[$this->defaultBacktraceIndex['file']]) 			? $backtrace[$this->defaultBacktraceIndex['file']]['file'] 			: '',
		];
	}

	public function endLap(){
		$lapCount = count($this->laps) - 1;
		$this->laps[$lapCount]['end']   = microtime(true);
		$this->laps[$lapCount]['total'] = $this->formatTime($this->laps[$lapCount]['end'] - $this->laps[$lapCount]['start']);
	}

	public function stop() {
		$this->end_time = microtime(true);
		$this->endLap();
	}

	public function summary($print = false) {
		// $this->removeStartsEnds();

		$return = [
			'start'  => $this->formatDate($this->start_time),
			'end'    => $this->formatDate($this->end_time),
			"memory" => memory_get_peak_usage(true) / 1048576 . ' Mb',
			'total'  => $this->formatTime($this->end_time - $this->start_time),
			'laps'   => $this->laps,
			'xhprof' => $this->xhprof
		];

		if(!empty($print)){
			debug2($return);
		}

		return $return;
	}

	public function formatDate() {
		$DateTime = \DateTime::createFromFormat('U.u', $this->start_time);
		$DateTime->setTimezone(new \DateTimeZone($this->time_zone));

		return $DateTime->format("Y-m-d H:i:s.u");
	}


	public function formatTime($microtime) {
		if (empty($microtime)) {
			return 0;
		}

		$total = new MicroTime($microtime);

		// if(empty($total->hours) && empty($total->minutes) && empty($total->seconds) && empty($total->milliseconds)){
		// 	return $microtime . ' microseconds';
		// }

		$hours   = strlen($total->hours) 	== 1 ? '0' . $total->hours : $total->hours;
		$minutes = strlen($total->minutes)	== 1 ? '0' . $total->minutes : $total->minutes;
		$seconds = strlen($total->seconds)	== 1 ? '0' . $total->seconds : $total->seconds;

		return $hours . ':' . $minutes . ':' . $seconds . '.' . $total->milliseconds;
	}

	public function removeStartsEnds() {
		foreach ($this->laps as $index => $lap) {
			unset($this->laps[$index]['start']);
			unset($this->laps[$index]['end']);
		}
	}

	public function setXhprof($xhprof){
		$this->xhprof = $xhprof;
		return $this;
	}

	public function getXhprof(){
		return $this->xhprof;
	}
}