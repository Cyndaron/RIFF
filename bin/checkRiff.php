#!/usr/bin/env php
<?php

namespace Cyndaron\RIFF;

use Cyndaron\BinaryHandler\BinaryReader;
use Cyndaron\BinaryHandler\Reader\IntegerDecoder;

require __DIR__ . '/../vendor/autoload.php';

$filename = $argv[1] ?? '';
if (empty($filename))
{
    echo "checkRiff.php <filename>\n";
    exit(1);
}

if (!file_exists($filename))
{
    echo "File does not exist!\n";
    exit(2);
}

function printInfo(Chunk $chunk, int $level = 0, bool $printContents = false): void
{
    $indentation = str_repeat('  ', $level);

    echo "{$indentation}Chunk name: {$chunk->name}\n";
    echo "{$indentation}Chunk size: {$chunk->getInnerSize()}\n";
    if ($chunk->contents instanceof SubChunks)
    {
        echo "{$indentation}Subchunks name: {$chunk->contents->name}\n";
        $printContents = $chunk->contents instanceof ListInfoChunk;
        foreach ($chunk->contents->chunks as $subChunk)
        {
            printInfo($subChunk, $level + 1, $printContents);
        }
    }
    elseif ($chunk->name === 'DISP')
    {
        $identifier = IntegerDecoder::decodeUint32(substr($chunk->contents, 0, 4));
        $type = DISPChunkType::tryFrom($identifier);
        $contents = substr($chunk->contents, 4);

        switch ($type)
        {
            case DISPChunkType::TEXT:
                echo "{$indentation}Chunk is label, contents: {$contents}\n";
                break;
            case DISPChunkType::DIB:
                //$contentsReader = \Cyndaron\BinaryHandler\BinaryReader::fromString($contents);
                $sha1 = sha1($contents);
                //$bitmapFilename = "{$sha1}.bmp";
                $dibFilename = __DIR__ . "/output/{$sha1}.dib";
                //$writer = BinaryWriter::fromFile($bitmapFilename);

                file_put_contents($dibFilename, $contents);

                //\Cyndaron\Util\BMP\DIBToBMPWriter::writeDIBToBMP($contentsReader, $writer);

                echo "{$indentation}Chunk is bitmap, extracted to: {$dibFilename}\n";
                break;
        }
    }
    elseif ($printContents)
    {
        $contents = rtrim($chunk->contents, "\x00");
        echo "{$indentation}Chunk contents: {$contents}\n";
    }
    echo "\n";
}

$reader = BinaryReader::fromFile($filename);
$riff = Chunk::read($reader);
printInfo($riff);
