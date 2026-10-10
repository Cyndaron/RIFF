<?php
declare(strict_types=1);

namespace Cyndaron\RIFF;

use Cyndaron\BinaryHandler\Reader\Interfaces\IntegerReaderInterface;
use Cyndaron\BinaryHandler\Reader\Interfaces\ReaderInterface;
use Cyndaron\BinaryHandler\Writer\Interfaces\IntegerWriterInterface;
use Cyndaron\BinaryHandler\Writer\Interfaces\WriterInterface;

class SubChunks
{
    public const NAME_LENGTH = 4;

    public string $name;
    /** @var list<Chunk> */
    public array $chunks = [];

    public static function read(ReaderInterface&IntegerReaderInterface $reader, int $length): self
    {
        $ret = new self();
        $ret->name = $reader->readBytes(self::NAME_LENGTH);

        $subchunksLength = $length - self::NAME_LENGTH;
        $endPos = $reader->getPosition() + $subchunksLength;
        while ($reader->getPosition() < $endPos)
        {
            $ret->chunks[] = Chunk::read($reader);
        }

        return $ret;
    }

    public function getOuterSize(): int
    {
        $total = self::NAME_LENGTH;
        foreach ($this->chunks as $chunk)
        {
            $subChunkSize = $chunk->getOuterSize();
            if ($subChunkSize & 1)
            {
                $subChunkSize++;
            }
            $total += $subChunkSize;
        }

        return $total;
    }

    public function write(WriterInterface&IntegerWriterInterface $writer): void
    {
        $writer->writeBytes($this->name);
        foreach ($this->chunks as $chunk)
        {
            $chunk->write($writer);
        }
    }

    public function addOrOverwrite(Chunk $chunk): void
    {
        foreach ($this->chunks as &$existingChunk)
        {
            if ($existingChunk->name === $chunk->name)
            {
                $existingChunk = $chunk;
                return;
            }
        }

        $this->chunks[] = $chunk;
    }
}
