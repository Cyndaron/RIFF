<?php
declare(strict_types=1);

namespace Cyndaron\RIFF;

use Cyndaron\BinaryHandler\Reader\Interfaces\IntegerReaderInterface;
use Cyndaron\BinaryHandler\Reader\Interfaces\ReaderInterface;
use Cyndaron\BinaryHandler\Writer\Interfaces\IntegerWriterInterface;
use Cyndaron\BinaryHandler\Writer\Interfaces\WriterInterface;
use function in_array;
use function is_string;
use function strlen;

final class Chunk
{
    public const NAME_LENGTH = 4;
    public const SIZE_LENGTH = 4;

    public const CHUNK_WITH_SUBCHUNKS = [
        'RIFF',
        'LIST',
    ];

    public function __construct(
        public string $name = 'JUNK',
        public SubChunks|string $contents = '',
    ) {
    }

    public function getInnerSize(): int
    {
        if (is_string($this->contents))
        {
            return strlen($this->contents);
        }

        return $this->contents->getOuterSize();
    }

    public function getOuterSize(): int
    {
        return $this->getInnerSize() + self::NAME_LENGTH + self::SIZE_LENGTH;
    }

    public static function read(ReaderInterface&IntegerReaderInterface $reader): self
    {
        $ret = new self();
        $ret->name = $reader->readBytes(self::NAME_LENGTH);
        $length = $reader->readUint32();
        if (in_array($ret->name, self::CHUNK_WITH_SUBCHUNKS, true))
        {
            $ret->contents = SubChunks::read($reader, $length);
            if ($ret->name === 'LIST' && $ret->contents->name === 'INFO')
            {
                $ret->contents = ListInfoChunk::fromRegular($ret->contents);
            }
        }
        else
        {
            $ret->contents = $reader->readBytes($length);
        }

        if ($length & 1)
        {
            $reader->seek(1);
        }

        return $ret;
    }

    public function write(WriterInterface&IntegerWriterInterface $writer): void
    {
        if (strlen($this->name) !== self::NAME_LENGTH)
        {
            throw new \RuntimeException('Chunk name has incorrect length!');
        }
        $writer->writeBytes($this->name);
        $size = $this->getInnerSize();
        $writer->writeUint32($size);
        if ($this->contents instanceof SubChunks)
        {
            $this->contents->write($writer);
        }
        else
        {
            $writer->writeBytes($this->contents);
        }

        if ($size & 1)
        {
            $writer->writeUint8(0);
        }
    }
}
