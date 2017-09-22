<?php

use PHPUnit\Framework\TestCase;

require_once './advertisement.php';

class AdTest extends TestCase
{
    public function testAdFromMysql()
    {
        $advertisement = new Advertisement(new Rub(), new ArrayFormatter());
        $advertisementData = $advertisement->getAdRecord(1);

        $this->assertEquals([
            'id' => 1,
            'name' => 'AdName_FromMySQL',
            'text' => 'AdText_FromMySQL',
            'keywords' => 'Some Keywords',
            'price' => 600, // рублей
        ], $advertisementData);
    }

    public function testAdFromDaemon()
    {
        $advertisement = new Advertisement(new Rub(), new ArrayFormatter());
        $advertisementData = $advertisement->get_daemon_ad_info(1);

        $this->assertEquals([
            'id' => '1',
            'companyId' => '235678',
            'userId' => '12348',
            'name' => 'AdName_FromDaemon',
            'text' => 'AdText_FromDaemon',
            'price' => 660,
        ], $advertisementData);
    }

    public function testAdCurrencyRub()
    {
        $advertisement = new Advertisement(new Rub(), new ArrayFormatter());
        $advertisementData = $advertisement->getAdRecord(1);

        $this->assertEquals([
            'id' => 1,
            'name' => 'AdName_FromMySQL',
            'text' => 'AdText_FromMySQL',
            'keywords' => 'Some Keywords',
            'price' => 600, // рублей
        ], $advertisementData);
    }

    public function testAdCurrencyEur()
    {
        $advertisement = new Advertisement(new Eur(), new ArrayFormatter());
        $advertisementData = $advertisement->getAdRecord(1);

        $this->assertEquals([
            'id' => 1,
            'name' => 'AdName_FromMySQL',
            'text' => 'AdText_FromMySQL',
            'keywords' => 'Some Keywords',
            'price' => 11.9, // евро
        ], $advertisementData);
    }

    public function testAdFileLoggerEnabled()
    {
        $fileLoggerDecorator = new FileLoggerDecorator(new FileLogger('/path/to/log'));
        $fileLoggerDecorator->setEnabled(true);

        $advertisement = new Advertisement(new Rub(), new ArrayFormatter());

        if ($fileLoggerDecorator->getEnabled()) {
            $advertisement = new AdvertisementDecorator($advertisement, $fileLoggerDecorator);
        }

        $advertisement->getAdRecord(1);
        $advertisement->getAdRecord(2);

        $this->assertEquals(2, count($fileLoggerDecorator->getLog()));
    }

    public function testAdFileLoggerDisabled()
    {
        $fileLoggerDecorator = new FileLoggerDecorator(new FileLogger('/path/to/log'));
        $fileLoggerDecorator->setEnabled(false);

        $advertisement = new Advertisement(new Rub(), new ArrayFormatter());

        if ($fileLoggerDecorator->getEnabled()) {
            $advertisement = new AdvertisementDecorator($advertisement, $fileLoggerDecorator);
        }

        $advertisement->getAdRecord(1);
        $advertisement->getAdRecord(2);

        $this->assertEquals(0, count($fileLoggerDecorator->getLog()));
    }
}