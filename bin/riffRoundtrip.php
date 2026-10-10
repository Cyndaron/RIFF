#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$filename = $argv[1] ?? '';
$outputFilename = $argv[2] ?? '';
if (empty($filename) || empty($outputFilename))
{
    echo "riffRoundtrip.php <filename> <outputFilename> \n";
    exit(1);
}

if (!file_exists($filename))
{
    echo "File does not exist!\n";
    exit(2);
}

$reader = \Cyndaron\BinaryHandler\BinaryReader::fromFile($filename);
$riff = \Cyndaron\RIFF\Chunk::read($reader);
assert($riff->contents instanceof \Cyndaron\RIFF\SubChunks);

$writer = \Cyndaron\BinaryHandler\BinaryWriter::fromFile($outputFilename);
$riff->write($writer);
