<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class LeilaoDaoMock extends LeilaoDao
{
    private $leiloes = [];
    public function salva(Leilao $leilao): void
    {
        $this->leiloes[] = $leilao;
    }

    public function recuperarNaoFinalizados(): array
    {
        return array_filter($this->leiloes, function(Leilao $leilao) {
            return !$leilao->estaFinalizado();
        });
    }

    public function recuperarFinalizados(): array
    {
        return array_filter($this->leiloes, function(Leilao $leilao) {
            return $leilao->estaFinalizado();
        });
    }

    public function atualiza(Leilao $leilao)
    {

    }
}
class EncerradorTest extends TestCase {

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $fiat = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $variant = new Leilao('Variant 1972 0Km', new \DateTimeImmutable('10 days ago'));

        $leilaoDao = new LeilaoDaoMock();
        $leilaoDao->salva($fiat);
        $leilaoDao->salva($variant);

        $encerrador = new Encerrador($leilaoDao);
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();
        self::assertCount(2, $leiloes);
        self::assertEquals('Fiat 147 0Km', $leiloes[0]->recuperarDescricao());
        self::assertEquals('Variant 1972 0Km', $leiloes[1]->recuperarDescricao());
    }
}