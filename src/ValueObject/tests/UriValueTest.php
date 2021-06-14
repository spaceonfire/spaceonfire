<?php

declare(strict_types=1);

namespace spaceonfire\ValueObject;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriValueTest extends TestCase
{
    public function testConstructor(): void
    {
        $val = new UriValue('http://localhost');
        self::assertInstanceOf(UriInterface::class, $val->value());
        self::assertSame('http://localhost', (string)$val);
        self::assertSame('"http:\/\/localhost"', json_encode($val, JSON_THROW_ON_ERROR));

        $val = new UriValue(new Uri('http://localhost'));
        self::assertInstanceOf(UriInterface::class, $val->value());
        self::assertSame('http://localhost', (string)$val);
        self::assertSame('"http:\/\/localhost"', json_encode($val, JSON_THROW_ON_ERROR));
    }

    public function testConstructorException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new UriValue('http://:80');
    }

    public function testConstructFailWithObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new UriValue(new \stdClass());
    }
}
