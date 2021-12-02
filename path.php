<?php

$pathways = [];

if (($csvFile = fopen("{$argv[1]}", 'r')) !== FALSE) {
	while (($data = fgetcsv($csvFile)) !== FALSE) {
		$pathways[] = [
			'origin' => $data[0],
			'destination' => $data[1],
			'time' => $data[2]
		];		
	}
	fclose($csvFile);
	$pathways = array_merge($pathways, reversePathways($pathways));
}

var_dump($pathways);

while (true) {
	echo 'Please input [Device From] [Device To] [Time] (e.g A F 1000 followed by ENTER key): ';
	$input = fopen('php://stdin', 'r');
	$line = strtoupper(fgets($input));
	$inputArray = explode(' ', $line);
	$origin = $inputArray[0];
	$destination = $inputArray[1];
	$maxTime = $inputArray[2];
	$matchingPath = findMatchingPath($pathways, [$origin], $destination, $maxTime);
	echo $matchingPath ?: 'Path not found' . PHP_EOL;
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

function reversePathways($pathways)
{
	foreach ($pathways as &$pathway) {
		$oldOrigin = $pathway['origin'];
		$pathway['origin'] = $pathway['destination'];
		$pathway['destination'] = $oldOrigin;
	}
	return $pathways;
}

?>