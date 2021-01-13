<?php

declare( strict_types = 1 );

namespace DNB\GND\UseCases\ImportItems;

class ImportStats {

	private float $startTime;
	private int $successCount = 0;
	private int $failureCount = 0;

	public function recordStart(): void {
		$this->startTime = (float)hrtime(true);
	}

	public function recordFailure(): void {
		$this->failureCount++;
	}

	public function recordSuccess(): void {
		$this->successCount++;
	}

	public function getDurationInSeconds(): float {
		return ( (float)hrtime(true) - $this->startTime ) / 1000000000;
	}

	public function getItemCount(): int {
		return $this->successCount + $this->failureCount;
	}

	public function getItemsPerSecond(): float {
		return $this->getItemCount() / $this->getDurationInSeconds();
	}

	public function getFailureCount(): int {
		return $this->failureCount;
	}

	public function getFailurePercentage(): float {
		return $this->failureCount / $this->getItemCount() * 100;
	}

}
