<?php

namespace Felideo\Performance;

class Xhprof {
	private $data       = [];
	private $filtered   = 0;
	private $dicionario = [];
	private $clean      = true;

	private $filter = [
		'from' => [
			'???_op',
			'Doctrine_',
			'Composer\Autoload'
		],
		'to' => [
			'???_op',
			'Doctrine_',
			'Composer\Autoload',
		]
	];


	public function executar($data){
		$this->clean_data($data);
		// $this->
		$this->clean_filter();
		$this->filter();

		$counter = 1;
		$max     = \count($this->data);

		// debug2(count($this->data));

		// for ($i=0; $i < 20; $i++) {
			// code...

			foreach ($this->data as $index => $item) {
				$index_to_push = $this->find_next_index_to_push($item['from']);

				if(empty($index_to_push)){
					continue;
					debug2('deu merda, da uma olhada nisso');
					debug2($this->data);

					exit;
				}

				$debug = [
					'index'   => $index_to_push,
					'item'    => $item,
					'destino' => $this->data[$index_to_push],
				];

				$this->data[$index_to_push]['children'][$item['to']] = $item;

				if(!empty($this->clean)){
					unset($this->data[$index_to_push]['children'][$item['to']]['from']);
					unset($this->data[$index_to_push]['children'][$item['to']]['to']);
					unset($this->data[$index_to_push]['children'][$item['to']]['index']);

					if(empty($this->data[$index_to_push]['children'][$item['to']]['children'])){
						unset($this->data[$index_to_push]['children'][$item['to']]['children']);
					}
				}

				unset($this->data[$index]);

				if($counter == 10 && $item['from'] == 'main()'){
					debug2($debug);
					debug2($this->data);
					exit;
				}


				// return $counter += 1;

				// $debug = [
				// 	'index'   => $index_to_push,
				// 	'item'    => $item,
				// 	'destino' => $this->data[$index_to_push],
				// ];

				// debug2($debug);
				// exit;

				// debug2($this->data[$index_to_push]['children']);
				// debug2($item);

				// // debug2($index_to_push);
				// // debug2($this->data);
				// exit;

				// exit;
			}
		// }

		// debug($this->filtered);
		// debug2($this->data, \count($this->data));
		// exit;
		//
		return $this->data;



















		while ($counter < $max) {
			$counter = $this->push_children($counter);

			$debug = [
				'counter' => $counter,
				'total' => count($this->data),
				'data' => $this->data,
			];

			if($counter == 10){
				debug2($debug);
				exit;
			}

			// debug2('cagou');
			// exit;
		}


		debug2($counter);
		debug2($this->data);
		exit;



		debug2($this->data);
		exit;


		// $result = preg_match("#^startText(.*)$#i", $string);

		$novos_data = [];
		$dicionario  = [];

		foreach ($this->data as $index => $dado) {
			$dicionario[] = $index;

			$parent = explode('==>', $index);

			$lero = [
				'from_to'  => $index,
				'from'     => isset($parent[1]) ? $parent[0] : null,
				'to'       => isset($parent[1]) ? $parent[1] : $parent[0],
				'children' => [],
			];

			$novos_data[] = array_merge($dado, $lero);
		}

		debug2($dicionario);
		debug2($this->data);
		debug2($novos_data);

		exit;

		// $this->data = array_reverse($data);
		// $this->mount_dictionary_and_parents();
		// $this->definir_altura();



		debug2($this->data);
		debug2($this->dicionario);

	}

	private function clean_data($data){
		foreach ($data as $index => $item) {
			$origin = explode('==>', $index);

			$this->data[$index] = [
				'ct'       => $item['ct'],
				'wto'      => $item['wt'],
				'wt'       => $item['wt'] < 1000000
					? $item['wt'] = number_format($item['wt'] / 1000, 3) . ' milliseconds'
					: $item['wt'] = number_format($item['wt'] / 1000000, 3) . ' seconds',

				'from'     => isset($origin[1]) ? $origin[0] : null,
				'to'       => isset($origin[1]) ? $origin[1] : $origin[0],
				'index'    => $index,
				'children' => [],
			];

			if($this->data[$index]['from'] != 'main()' && $this->data[$index]['to'] != 'main()' &&  $item['wt'] < 1000000){
				$this->filtered += $this->data[$index]['wto'];
				unset($this->data[$index]);
			}
		}
	}

	private function clean_filter(){
		foreach ($this->filter as $index_01 => $types) {
			foreach ($types as $index_02 => $filter) {
				if($filter == 'main()'){
					unset($this->filter[$index_01][$index_02]);
					continue;
				}

				$this->filter[$index_01][$index_02] = trim($filter);
			}
		}
	}

	private function filter(){
		foreach ($this->data as $index_01 => $item) {
			$call = \explode('==>', $index_01);

			$match = false;

			foreach ($this->filter['to'] as $index_02 => $filter) {
				$wich_call = isset($call[1]) ? $call[1] : $call[0];
				$match     = \preg_match("/^" . preg_quote($filter, '/') . "/", $wich_call);

				if(!empty($match)){
					$this->filtered += $item['wt'];
					unset($this->data[$index_01]);
					break;
				}
			}

			if(!empty($match)){
				continue;
			}

			foreach ($this->filter['from'] as $index_02 => $filter) {
				$wich_call = $call[0];
				$match     = \preg_match("/^" . preg_quote($filter, '/') . "/", $wich_call);

				if(!empty($match)){
					$this->filtered += $item['wto'];
					unset($this->data[$index_01]);
					break;
				}
			}

			if(!empty($match)){
				continue;
			}

			// debug2($call);
			// exit;

			// if(!isset($call[1])){
			// 	continue;
			// }

			// foreach ($this->filter['from'] as $index_02 => $filter) {
			// 	$match = \preg_match("/^" . preg_quote($filter, '/') . "/", $call[1]);

			// 	if(!empty($match)){
			// 		$this->filtered += $item['wt'];
			// 		unset($this->data[$index_01]);
			// 	}
			// }

		}
	}

	private function push_children($counter){
		// debug2($this->data);

		foreach ($this->data as $index => $item) {
			$index_to_push = $this->find_next_index_to_push($item['from']);

			if(empty($index_to_push)){
				debug2('deu merda, da uma olhada nisso');
				debug2($this->data);

				exit;
			}

			$debug = [
				'index'   => $index_to_push,
				'item'    => $item,
				'destino' => $this->data[$index_to_push],
			];

			$this->data[$index_to_push]['children'][$item['to']] = $item;

			if(!empty($this->clean)){
				unset($this->data[$index_to_push]['children'][$item['to']]['from']);
				unset($this->data[$index_to_push]['children'][$item['to']]['to']);
				unset($this->data[$index_to_push]['children'][$item['to']]['index']);

				if(empty($this->data[$index_to_push]['children'][$item['to']]['children'])){
					unset($this->data[$index_to_push]['children'][$item['to']]['children']);
				}
			}

			unset($this->data[$index]);

			if($counter == 7){
				debug2($debug);
				debug2($this->data);
				exit;
			}


			return $counter += 1;

			// $debug = [
			// 	'index'   => $index_to_push,
			// 	'item'    => $item,
			// 	'destino' => $this->data[$index_to_push],
			// ];

			// debug2($debug);
			// exit;

			// debug2($this->data[$index_to_push]['children']);
			// debug2($item);

			// // debug2($index_to_push);
			// // debug2($this->data);
			// exit;

			// exit;
		}
	}

	private function find_next_index_to_push($from){
		foreach ($this->data as $index => $item) {
			if($item['to'] == $from){
				return $index;
			}
		}

		return null;
	}


}