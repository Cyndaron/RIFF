#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

$filename = $argv[1] ?? '';
$outputFilename = $argv[2] ?? '';
$title = $argv[3] ?? '';
$composer = $argv[4] ?? '';
$genre = $argv[5] ?? '';
if (empty($filename) || empty($outputFilename) || empty($title) || empty($composer))
{
    echo "riffInjectInfo.php <filename> <outputFilename> <title> <composer>\n";
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

$listChunk = null;
foreach ($riff->contents->chunks as $chunk)
{
    if ($chunk->name === 'LIST' && $chunk->contents instanceof \Cyndaron\RIFF\ListInfoChunk)
    {
        $listChunk = $chunk;
        break;
    }
}
if ($listChunk === null)
{
    $listChunk = new \Cyndaron\RIFF\Chunk('LIST');
    $listChunk->contents = new \Cyndaron\RIFF\ListInfoChunk();
}

$listChunk->contents->setTitle($title);
$listChunk->contents->setComposer($composer);
$listChunk->contents->setGenre($genre);

$riff->contents->chunks[] = $listChunk;

$writer = \Cyndaron\BinaryHandler\BinaryWriter::fromFile($outputFilename);
$riff->write($writer);
