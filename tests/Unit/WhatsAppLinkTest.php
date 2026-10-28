<?php

namespace Tests\Unit;

use App\Support\WhatsAppLink;
use PHPUnit\Framework\TestCase;

class WhatsAppLinkTest extends TestCase
{
    public function test_strips_dashes_for_local_display(): void
    {
        $this->assertSame('081323567354', WhatsAppLink::localDisplay('0813-2356-7354'));
        $this->assertSame('081323567354', WhatsAppLink::localDisplay('0813 2356 7354'));
    }

    public function test_converts_indonesian_mobile_to_whatsapp_web_url(): void
    {
        $this->assertSame(
            'https://web.whatsapp.com/send?phone=6281323567354',
            WhatsAppLink::url('0813-2356-7354')
        );
    }

    public function test_appends_message_text(): void
    {
        $this->assertSame(
            'https://web.whatsapp.com/send?phone=6281323567354&text=Halo',
            WhatsAppLink::url('0813-2356-7354', 'Halo')
        );
    }

    public function test_app_url_opens_whatsapp_app_or_web(): void
    {
        $this->assertSame(
            'https://wa.me/6281323567354?text=Halo',
            WhatsAppLink::appUrl('0813-2356-7354', 'Halo')
        );
        $this->assertSame(
            'whatsapp://send?phone=6281323567354&text=Halo',
            WhatsAppLink::nativeUrl('0813-2356-7354', 'Halo')
        );
    }

    public function test_accepts_plus_country_code(): void
    {
        $this->assertSame(
            'https://web.whatsapp.com/send?phone=6281323567354',
            WhatsAppLink::url('+62 813 2356 7354')
        );
    }

    public function test_prefixes_local_eight_without_zero(): void
    {
        $this->assertSame(
            'https://web.whatsapp.com/send?phone=6281323567354',
            WhatsAppLink::url('81323567354')
        );
    }

    public function test_returns_null_for_invalid_phone(): void
    {
        $this->assertNull(WhatsAppLink::url(''));
        $this->assertNull(WhatsAppLink::url('123'));
        $this->assertNull(WhatsAppLink::url('not-a-phone'));
    }
}
