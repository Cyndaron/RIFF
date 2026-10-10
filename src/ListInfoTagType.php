<?php
declare(strict_types=1);

namespace Cyndaron\RIFF;

/**
 * @see https://exiftool.sourceforge.net/TagNames/RIFF.html#Info
 */
enum ListInfoTagType : string
{
    case COMPOSER = 'ICMP';
    case GENRE = 'IGNR';
    case TITLE = 'INAM';
}
