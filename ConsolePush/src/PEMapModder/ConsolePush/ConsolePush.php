<?php

declare(strict_types=1);

namespace PEMapModder\ConsolePush;

use pocketmine\event\Listener;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\plugin\PluginBase;

class ConsolePush extends PluginBase implements Listener {

	private $bufferEnabled = false;
	private $innerBuffer = "";

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function e(ServerCommandEvent $event) {
		$cmd = $event->getCommand();
		if ($cmd === "\\") {
			if ($this->bufferEnabled) {
				$this->stopBuffering();
			} else {
				$this->startBuffering();
			}
			$event->setCancelled();
			return;
		}

		if ($cmd === "\\f") {
			if (!$this->bufferEnabled) {
				$event->setCancelled();
				$this->getLogger()->error('Buffer is not enabled, nothing to flush!');
				return;
			}
			ob_flush();
			return;
		}

		$cmd = $this->innerBuffer . (strpos($cmd, "\\ ") === 0 ? substr($cmd, 1) : $cmd);
		$this->innerBuffer = "";
		if ((strlen($cmd) > 2) && ($cmd{strlen($cmd) - 2} === "\\")) {
			switch ($cmd{strlen($cmd) - 1}) {
				case "n":
					$cmd = substr($cmd, 0, -2);
					break;
				case "t":
					$cmd = rtrim(substr($cmd, 0, -2), " ");
					break;
				case "c":
					$event->setCancelled();
					return;
				case "p":
					$this->innerBuffer = substr($cmd, 0, -2);
					echo "> " . $this->innerBuffer;
					if (!$this->bufferEnabled) {
						$this->startBuffering();
					}
					$event->setCancelled();
					return;
			}
		}
		$event->setCommand($cmd);

		if ($this->bufferEnabled) {
			$this->stopBuffering();
		}
	}

	private function startBuffering() {
		$this->bufferEnabled = true;
		ob_start();
	}

	private function stopBuffering() {
		$this->bufferEnabled = false;
		ob_end_flush();
		$this->getLogger()->info('Stopped buffering');
	}

}
