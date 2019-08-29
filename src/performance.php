<?php

function performance_start(){
	$performance = \Felideo\Performance\Timer::get_timer();
	$performance->setTimeZone('America/Sao_Paulo')
		->defaultBacktraceIndex(2, 1, 2, 1)
		->reset()
		->start();
}

function performance_lap($name = null){
	$_SESSION['performance_check']->endLap();
	$_SESSION['performance_check']->lap($name);
}

function performance_stop($print = true){
	$_SESSION['performance_check']->stop();
	return $_SESSION['performance_check']->summary($print);
}
