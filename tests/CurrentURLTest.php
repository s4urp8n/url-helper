<?php

use Zver\CurrentURL;

class CurrentURLTest extends PHPUnit\Framework\TestCase
{

    use \Zver\Package\Helper;

    public function testGetData()
    {
        $testData = [
            'http://localhost:55/test/test/'               => [
                'HOST'  => 'localhost',
                'HTTPS' => false,
                'GET'   => [],
                'PORT'  => '55',
                'PATH'  => '/test/test',
            ],
            'http://localhost:55/test/test/?page=1&rate=2' => [
                'HOST'  => 'localhost',
                'HTTPS' => false,
                'GET'   => ['page' => '1', 'rate' => '2'],
                'PORT'  => '55',
                'PATH'  => '/test/test',
            ],
            'http://localhost:55/test/test?page=1&rate=2'  => [
                'HOST'  => 'localhost',
                'HTTPS' => false,
                'GET'   => ['page' => '1', 'rate' => '2'],
                'PORT'  => '55',
                'PATH'  => '/test/test',
            ]
        ];

        foreach ($testData as $url => $urlData) {
            $content = unserialize(file_get_contents($url));
            $this->assertSame($content->getData(), $urlData);
            $this->assertFalse($content->isSecure());
        }
    }

    public function testGetURL()
    {
        $testData = [
            'http://localhost:55/test/test/'                  => [
                'callback' => function (CurrentURL $url) {
                    return $url->get();
                },
                'result'   => 'http://localhost:55/test/test',
            ],
            'http://localhost:55/test/test/test/?page=1&rr=3' => [
                'callback' => function (CurrentURL $url) {
                    return $url->removePath()
                               ->removeQuery()
                               ->setQueryParam('page', 4)
                               ->get();
                },
                'result'   => 'http://localhost:55/?page=4',
            ],
            'http://localhost:55/test/test/test/?page=1&rr=3' => [
                'callback' => function (CurrentURL $url) {
                    return $url->setQueryParam('page', 4)
                               ->setPath('work')
                               ->get();
                },
                'result'   => 'http://localhost:55/work?page=4&rr=3',
            ],
            'http://localhost:55/test/test/test/?page=1&rr=3' => [
                'callback' => function (CurrentURL $url) {
                    return $url->setQueryParam('page', 4)
                               ->setPath('/work/test/')
                               ->get();
                },
                'result'   => 'http://localhost:55/work/test?page=4&rr=3',
            ],
            'http://localhost:55/test/test/test/?page=1&rr=3' => [
                'callback' => function (CurrentURL $url) {
                    return $url->setQueryParam('page', 4)
                               ->setPath('/work/test/')
                               ->setPort(80)
                               ->get();
                },
                'result'   => 'http://localhost/work/test?page=4&rr=3',
            ],
            'http://localhost:55/test/test/test/?page=1&rr=3' => [
                'callback' => function (CurrentURL $url) {
                    return $url->setQueryParam('page', 4)
                               ->setPath('/work/test/')
                               ->setPort(443)
                               ->get();
                },
                'result'   => 'http://localhost/work/test?page=4&rr=3',
            ]
        ];

        foreach ($testData as $url => $urlData) {
            $content = unserialize(file_get_contents($url));
            $this->assertSame($urlData['callback']($content), $urlData['result']);
        }
    }

}