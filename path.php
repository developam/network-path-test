<?php

init($argv);

function init($arguments)
{
	$pathways = getPathwaysFromCsv($arguments[1]);
	while (true) {
		$input = getInput();
		$matchingPath = findMatchingPath($pathways, [$input['origin']], $input['destination'], $input['maxTime']);
		echo $matchingPath ?: 'Path not found' . PHP_EOL;
	}
}

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

function getInput()
{
	echo 'Please input [Device From] [Device To] [Time] (e.g A F 1000 followed by ENTER key): ';
	$input = fopen('php://stdin', 'r');
	$line = strtoupper(fgets($input));
	$lineArray = explode(' ', $line);
	$parsed = [
		'origin' => $lineArray[0],
		'destination' => $lineArray[1],
		'maxTime' => $lineArray[2]
	];
	return $parsed;
}

function findMatchingPath($pathways, $followedNodes, $destinationNode, $maxTime, $accumulatedTime = 0)
{
	$origin = end($followedNodes);

	foreach ($pathways as $pathway) {
		if ($pathway['origin'] === $origin) {
			if (in_array($pathway['destination'], $followedNodes)) {
				continue;
			}
			$tempFollowedNodes = array_merge($followedNodes, [$pathway['destination']]);
			$tempAccumulatedTime = $accumulatedTime + $pathway['time'];
			if ($pathway['destination'] === $destinationNode && $tempAccumulatedTime <= $maxTime) {
				foreach ($tempFollowedNodes as $followedNode) {
					$output .= $followedNode . ' => ';
				}
				$output .= $tempAccumulatedTime . PHP_EOL;
				return $output;
			} else {
				$recursiveOutput = findMatchingPath($pathways, $tempFollowedNodes, $destinationNode, $maxTime, $tempAccumulatedTime);
				if (null !== $recursiveOutput) {
					return $recursiveOutput;
				}
			}
		}
	}
}

?>