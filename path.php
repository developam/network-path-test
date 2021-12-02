<?php

init($argv);

/**
 * Run the program
 */
function init($arguments)
{
	$pathways = getPathwaysFromCsv($arguments[1]);
	while (true) {
		$input = getInput();
		$matchingPath = findMatchingPath($pathways, [$input['origin']], $input['destination'], $input['maxTime']);
		echo $matchingPath ?: 'Path not found' . PHP_EOL;
	}
}

/**
 * Returns array of pathways and latencies from CSV file
 */
function getPathwaysFromCsv($filename)
{
	$pathways = [];
	if (($csvFile = fopen($filename, 'r')) !== FALSE) {
		while (($data = fgetcsv($csvFile)) !== FALSE) {
			$pathways[] = [
				'origin' => $data[0],
				'destination' => $data[1],
				'time' => $data[2]
			];
			$pathways[] = [
				'origin' => $data[1],
				'destination' => $data[0],
				'time' => $data[2]
			];
		}
		fclose($csvFile);
	}
	return $pathways;
}

/**
 * Prompt for user input and parse result
 */
function getInput()
{
	echo 'Please input [Device From] [Device To] [Time] (e.g A F 1000 followed by ENTER key): ';
	$input = fopen('php://stdin', 'r');
	$line = strtoupper(fgets($input));
	if (trim($line) == 'QUIT') {
		echo 'Goodbye!' . PHP_EOL;
		exit;
	}
	$lineArray = explode(' ', $line);
	$parsed = [
		'origin' => $lineArray[0],
		'destination' => $lineArray[1],
		'maxTime' => $lineArray[2]
	];
	return $parsed;
}

/**
 * Recursively search pathways to find the first path to the final destination node
 */
function findMatchingPath($pathways, $followedNodes, $finalDestination, $maxTime, $accumulatedTime = 0)
{
	$currentPosition = end($followedNodes);

	foreach ($pathways as $pathway) {
		if ($pathway['origin'] === $currentPosition && !in_array($pathway['destination'], $followedNodes)) {
			$newFollowedNodes = array_merge($followedNodes, [$pathway['destination']]);
			$newAccumulatedTime = $accumulatedTime + $pathway['time'];
			if ($pathway['destination'] === $finalDestination && $newAccumulatedTime <= $maxTime) {
				return formatOutput($newFollowedNodes, $newAccumulatedTime);
			} else {
				$recursiveOutput = findMatchingPath(
					$pathways,
					$newFollowedNodes,
					$finalDestination,
					$maxTime,
					$newAccumulatedTime
				);
				if (null !== $recursiveOutput) {
					return $recursiveOutput;
				}
			}
		}
	}
}

/**
 * Format output string of all nodes and total time
 */
function formatOutput($nodes, $time)
{
	foreach ($nodes as $node) {
		$output .= $node . ' => ';
	}
	$output .= $time . PHP_EOL;
	return $output;
}

?>