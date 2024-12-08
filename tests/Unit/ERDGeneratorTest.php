<?php
namespace Tests\Unit;


use Exception;
use PHPUnit\Framework\TestCase;
use Rtcoder\LaravelERD\Services\ERDGenerator;

class ERDGeneratorTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function test_it_can_generate_erd(): void
    {
        $generator = new ERDGenerator();
        $result = $generator->generate('pdf', '/tmp');
        $this->assertFileExists($result);
    }
}
