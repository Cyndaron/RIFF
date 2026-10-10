<?php
declare(strict_types=1);

namespace Cyndaron\RIFF;

use function assert;

class ListInfoChunk extends SubChunks
{
    public function __construct()
    {
        $this->name = 'INFO';
    }

    private function addNullTerminator(string $input): string
    {
        return $input . "\x00";
    }

    private function addOrOverwriteTag(ListInfoTagType $tag, string $contents): void
    {
        $chunk = new Chunk($tag->value, $this->addNullTerminator($contents));
        $this->addOrOverwrite($chunk);
    }

    public function setTitle(string $text): void
    {
        $this->addOrOverwriteTag(ListInfoTagType::TITLE, $text);
    }

    public function setComposer(string $text): void
    {
        $this->addOrOverwriteTag(ListInfoTagType::COMPOSER, $text);
    }

    public function setGenre(string $text): void
    {
        $this->addOrOverwriteTag(ListInfoTagType::GENRE, $text);
    }

    public static function fromRegular(SubChunks $regular): self
    {
        assert($regular->name === 'INFO');
        $ret = new self();
        $ret->chunks = $regular->chunks;
        return $ret;
    }
}
