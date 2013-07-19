<?php
namespace yCrawler\Tests;
use yCrawler\Document;
use yCrawler\Parser;

class DocumentTest extends  \PHPUnit_Framework_TestCase
{
    public function createDocument($url)
    {
        $parser = new DocumentTest_ParserMock();

        return new Document($url, $parser);
    }

    public function testSetURL()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertInstanceOf(
            'yCrawler\Tests\DocumentTest_ParserMock',
            $doc->getParser()
        );
    }

    public function testEvaluate()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $doc->evaluate();
        $this->assertCount(4, $doc->getValuesStorage()->get('pre'));

    }

    public function testLinks()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $result = $doc->links();
        $this->assertSame(26, count($result));
    }

    public function testParse()
    {
        $url = 'http://httpbin.org/';
        $doc = $this->createDocument($url);

        $this->assertInstanceOf(
            'yCrawler\Document',
            $doc->parse()
        );

        $result = $doc->getValuesStorage()->get('pre');
        $this->assertSame(4, count($result));

        $result = $doc->getLinks();
        $this->assertSame(26, count($result));

        $this->assertTrue($doc->isParsed());
    }

    public function testIsVerified()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $this->assertTrue($doc->isVerified());
    }

    public function testIsIndexable()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $this->assertTrue($doc->isIndexable());
    }

    public function testEvaluateXPathNode()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $this->assertSame(4, count($doc->evaluateXPath('//pre')));
        $this->assertSame(1, count($doc->evaluateXPath('//pre[1]')));

        $result = $doc->evaluateXPath('//pre[1]');
        $this->assertTrue(isset($result[0]['value']));
        $this->assertTrue(isset($result[0]['node']));
        $this->assertTrue(isset($result[0]['dom']));

        $this->assertSame('{"origin": "24.127.96.129"}' . PHP_EOL, $result[0]['value']);
    }

    public function testEvaluateRegExp()
    {
        $url = 'http://httpbin.org/';

        $doc = $this->createDocument($url);
        $result = $doc->evaluateRegExp('/<code>(.*)<\/code>/');
        $this->assertSame(28, count($result));

        $this->assertTrue(isset($result[0]['value']));
        $this->assertTrue(isset($result[0]['full']));
     }
}

class DocumentTest_ParserMock extends Parser
{
    public function initialize()
    {
        $this->setStartupURL('http://httpbin.org/');

        $this->createLinkFollowItem('//a');
        $this->createVerifyItem('//a');

        $this->createValueItem('no-exists', '//no-exists-tag');
        $this->createValueItem('pre', '//pre');
    }
}
