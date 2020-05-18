<?php

declare(strict_types=1);

namespace SOFe\LessIsMore;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class LessIsMore extends PluginBase{
	public function onEnable() : void{
		$this->saveDefaultConfig();
		$genLpp = $this->getConfig()->get("LessIsMore", [])["lines per page"] ?? 5;
		$preferForms = $this->getConfig()->get("LessIsMore", [])["prefer forms"] ?? true;

		foreach($this->getConfig()->getAll() as $cmd => $options){
			if($cmd === "LessIsMore"){
				continue;
			}

			$description = "";
			$aliases = [];
			$pages = [];
			if(\is_array($options)){
				if(isset($options["lines"])){
					if(isset($options["description"])){
						$description = (string) $options["description"];
					}
					if(isset($options["aliases"])){
						$aliases = (array) $options["aliases"];
					}
					if(isset($options["page break"])){
						$break = $options["page break"];
						$lines = (array) $options["lines"];
						$temp = [];
						foreach($lines as $line){
							if($line === $break){
								if(!empty($temp)){
									$pages[] = $temp;
								}
								$temp = [];
							}else{
								$temp[] = $this->formatLine($cmd, $line);
							}
						}
						if(!empty($temp)){
							$pages[] = $temp;
						}
					}else{
						$lpp = (int) ($options["lines per page"] ?? $genLpp);
						$pages = $this->createPages($cmd, $options["lines"], $lpp);
					}
				}else{
					$pages = $this->createPages($cmd, $options, $genLpp);
				}
			}else{
				$pages[] = [$options];
			}

			$this->getServer()->getCommandMap()->register("lessismore", new LessCommand($this, $cmd, $description, $aliases, $pages, $preferForms));
		}
	}

	private function createPages(string $cmd, array $lines, int $lpp) : array{
		$output = [];
		$temp = [];
		foreach($lines as $line){
			$line = $this->formatLine($cmd, $line);
			if($line === ""){
				continue;
			}
			$temp[] = $line;
			if(\count($temp) >= $lpp){
				$output[] = $temp;
				$temp = [];
			}
		}
		if(\count($temp) !== 0){
			$output[] = $temp;
		}
		return $output;
	}

	private function formatLine(string $cmd, $line) : string{
		if(!\is_string($line)){
			if(\is_array($line)){
				$this->getLogger()->error("Error creating /$cmd: Cannot convert an array to string. Did you misspell \"lines\", or indent your config wrongly?");
				return "";
			}
			$line = (string) $line;
		}
		$line = TextFormat::colorize($line);
		return $line;
	}
}
