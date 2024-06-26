<?php

namespace Felideo\Performance;

class MicroTime
{
    public $hours;
    public $minutes;
    public $seconds;
    public $milliseconds;

    public function __construct($microtime)
    {
        $hours = (int)
            ($minutes = (int)
                ($seconds = (int)
                    ($milliseconds = (int)
                    ($microtime * 1000))
                    / 1000)
                / 60)
            / 60;

        $this->hours        = (int) $hours;
        $this->minutes      = (int) $minutes % 60;
        $this->seconds      = (int) $seconds % 60;
        $this->milliseconds = (int) $milliseconds % 1000;
    }

    public function toArray(){
        $data = [];

        if ($this->hours !== 0) {
            $data['hours'] = $this->hours;
        }

        if ($this->minutes !== 0) {
            $data['minutes'] = $this->minutes;
        }

        if ($this->seconds !== 0) {
            $data['seconds'] = $this->seconds;
        }

        $data['milliseconds'] = $this->milliseconds;

        return $data;
    }

    public function toString(){
        if ($this->hours !== 0) {
            return "Elapsed Time: {$this->hours} hour(s) {$this->minutes} minute(s) {$this->seconds} second(s) {$this->milliseconds} millisecond(s)";
        }

        if ($this->minutes !== 0) {
            return "Elapsed Time: {$this->minutes} minute(s) {$this->seconds} second(s) {$this->milliseconds} millisecond(s)";
        }

        if ($this->seconds !== 0) {
            return "Elapsed Time: {$this->seconds} second(s) {$this->milliseconds} millisecond(s)";
        }

        return "Elapsed Time: {$this->milliseconds} millisecond(s)";
    }
}
