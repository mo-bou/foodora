<?php

namespace App\Tests\Product\Services;

use App\Command\Product\ProductImportCsvCommand;
use App\Service\Product\MercurialImportService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class MercurialImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testStoreMercurialFileContents(): void
    {
        $sluggerMock = $this->createMock(SluggerInterface::class);
        $sluggerMock->expects(self::once())->method('slug')->willReturn(new UnicodeString('aaa'));

        $dirname = '/tmp/test'.uniqid();
        $importService = new MercurialImportService($dirname, $sluggerMock);
        $this->assertTrue(file_exists($dirname));
        $this->assertTrue(is_dir($dirname));
        $filename = $importService->storeMercurialFileContents('contents_mercurial', 'aaa');

        $this->assertTrue(file_exists($dirname.'/'.$filename));
        $this->assertEquals('contents_mercurial', file_get_contents($dirname.'/'.$filename));
    }

    public function testGetMercurialFileContents(): void
    {
        $sluggerMock = $this->createMock(SluggerInterface::class);

        $dirname = '/tmp/test' . uniqid();
        $filename = 'test.csv';
        $importService = new MercurialImportService($dirname, $sluggerMock);
        file_put_contents(filename: $dirname.'/'.$filename, data: 'test get contents');
        $contents = $importService->getMercurialFileContents($filename);
        self::assertEquals('test get contents', $contents);
    }
}
