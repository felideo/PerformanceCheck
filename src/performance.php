<?php

function performance_start($xhprof = false){
	$performance = \Felideo\Performance\Timer::get_timer();
	$performance->setTimeZone('America/Sao_Paulo')
		->defaultBacktraceIndex(2, 1, 2, 1)
		->reset()
		->start();

	if(!empty($xhprof)){
		$_SESSION['performance_check']->setXhprof(true);
		xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
	}
}

function performance_lap($name = null){
	$_SESSION['performance_check']->endLap();
	$_SESSION['performance_check']->lap($name);
}

function performance_stop($print = true){
	if(!empty($_SESSION['performance_check']->getXhprof())){
		$disable = xhprof_disable();
		$xhprof = new \Felideo\Performance\Xhprof();
		$_SESSION['performance_check']->setXhprof($xhprof->executar($disable));
	}

	$_SESSION['performance_check']->stop();

	return $_SESSION['performance_check']->summary($print);
}

function ok(){
	return;
}

function principal(){
	$ret_2 = secundaria(1);
	$ret_3 = terciaria($ret_2);

	return $ret_2 + $ret_3;
}

function secundaria($soma){
	$ret_1 = lero_0($soma);
	$ret_2 = lero_1($soma);
	return $ret_1 + $ret_2;
}

function terciaria($soma){
	$ret_1 = lero_0($soma);
	$ret_2 = lero_1($soma);
	return $ret_1 + $ret_2;
}

function lero_0($soma){
	return $soma + 1;
}

function lero_1($soma){
	sleep(1);
	return $soma + 2;
}


