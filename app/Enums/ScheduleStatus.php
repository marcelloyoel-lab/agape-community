<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case DRAFT = 'draft';
    case GENERATED = 'generated';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::GENERATED => 'Generated',
            self::PUBLISHED => 'Published',
            self::CANCELLED => 'Cancelled',
        };
    }
}

/*

Terakhir: Berhasil kirim gambar ke WhatsApp. Tapi sekarang ada Send, Edit, sama Cancel.
Nah, kita mau hapus Edit. Jadi cuman ada send sama cancel
Tapi ada dilema.

Trus gunanya status approved apa dong? 
Apa better kita hapus status approved dan diganti jadi generated aja ya? 
Generated = poster udah digenerate, tapi belum dikirim ke WhatsApp

Nah, abis itu abis generated ada pilihan send atau cancel. Kalo send, statusnya jadi published. Kalo cancel, statusnya jadi cancelled.

*/