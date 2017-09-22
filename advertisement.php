<?php

function getAdRecord($id)
{
    // пример ответа
    return [
        'id' => $id,
        'name' => 'AdName_FromMySQL',
        'text' => 'AdText_FromMySQL',
        'keywords' => 'Some Keywords',
        'price' => 10, // 10$
    ];
}

function get_daemon_ad_info($id)
{
    // пример ответа: строка разделенная табуляцией
    return "{$id}\t235678\t12348\tAdName_FromDaemon\tAdText_FromDaemon\t11";
}

interface ArrayFormatterInterface
{
    public function toArray($data);
}

class ArrayFormatter implements ArrayFormatterInterface
{
    public function toArray($data)
    {
        return array_combine(
            ['id', 'companyId', 'userId', 'name', 'text', 'price'],
            explode("\t", $data)
        );
    }
}

class Currency
{
    private $rate;

    public function __construct()
    {
        $this->rate = [
            'Rub' => 60,
            'Eur' => 1.19
        ];
    }

    public function convert($value)
    {
        if ($this instanceof Usd) {
            return $value;
        }

        return round($value * $this->rate[get_class($this)], 2);
    }

    public function getName() {
        return $this->name;
    }
}

class Usd extends Currency
{
    public $name = 'доллар';
}

class Rub extends Currency
{
    public $name = 'рубль';
}

class Eur extends Currency
{
    public $name = 'евро';
}

interface AdvertisementInterface
{
    public function getAdRecord($id);

    public function get_daemon_ad_info($id);
}

class Advertisement implements AdvertisementInterface
{
    private $currency;
    private $arrayFormatter;

    public function __construct(Currency $currency, ArrayFormatterInterface $arrayFormatter)
    {
        $this->currency = $currency;
        $this->arrayFormatter = $arrayFormatter;
    }

    public function getCurrencyName()
    {
        return $this->currency->getName();
    }

    public function getAdRecord($id)
    {
        $advertisementDb = getAdRecord($id);

        $price = $this->currency->convert($advertisementDb['price']);
        $advertisementDb['price'] = $price;

        return $advertisementDb;
    }

    public function get_daemon_ad_info($id)
    {
        $advertisementDaemon = get_daemon_ad_info($id);

        $advertisementDaemonArray = $this->arrayFormatter->toArray($advertisementDaemon);
        $price = $this->currency->convert($advertisementDaemonArray['price']);
        $advertisementDaemonArray['price'] = $price;

        return $advertisementDaemonArray;
    }
}

class FileLogger
{
    private $log;

    public function __construct($filename)
    {
    }

    public function log($message)
    {
        $this->log[] = $message;
    }

    public function getLog()
    {
        return $this->log;
    }
}

class FileLoggerDecorator
{
    private $fileLogger;
    private $enabled = true;

    public function __construct(FileLogger $fileLogger)
    {
        $this->fileLogger = $fileLogger;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function log($message)
    {
        if ($this->enabled) {
            $this->fileLogger->log($message);
        }
    }

    public function getLog()
    {
        return $this->fileLogger->getLog();
    }
}

class AdvertisementDecorator
{
    private $advertisement;
    private $logger;

    public function __construct(AdvertisementInterface $advertisement, $logger)
    {
        $this->advertisement = $advertisement;
        $this->logger = $logger;
    }

    public function __call($method, $parameters)
    {
        $this->logger->log('[' . date('H:i:s') . '] ' . $method . '(ID=' . $parameters[0] . ')');

        return call_user_func_array(array($this->advertisement, $method), $parameters);
    }
}

class View
{
    public function __construct($advertisement, $currency)
    {
        echo '
            <h1>' . $advertisement['name'] . '</h1>
            <p>' . $advertisement['text'] . '</p>
            <p>Стоимость: ' . $advertisement['price'] . ' ' . $currency . '</p>
        ';
    }
}