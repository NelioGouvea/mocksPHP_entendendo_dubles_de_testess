<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase {

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $fiat = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $variant = new Leilao('Variant 1972 0Km', new \DateTimeImmutable('10 days ago'));

        $leilaoDao = $this->createMock(LeilaoDao::class);
        $leilaoDao->method('recuperarFinalizados')
            ->willReturn([$fiat, $variant]);
        $leilaoDao->expects(self::once())
            ->method('recuperarNaoFinalizados')
            ->willReturn([$fiat, $variant]);

        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$fiat],
                [$variant]
            );


        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(2, $leiloes);
        static::assertTrue($leiloes[0]->estaFinalizado());
        static::assertTrue($leiloes[1]->estaFinalizado());
        self::assertEquals('Fiat 147 0Km', $leiloes[0]->recuperarDescricao());
        self::assertEquals('Variant 1972 0Km', $leiloes[1]->recuperarDescricao());
    }
}