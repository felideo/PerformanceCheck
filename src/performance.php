<?php

function performance_start(){
	Felideo\Performance\Timer::get_timer();
	debug2('exit');
	exit;
}

function performance_lap(){

}

function performance_stop(){

}