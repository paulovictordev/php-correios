<?php

namespace BrunoViana\Correios\Tests\CalculoPrecoPrazo\Services;

use BrunoViana\Correios\Tests\TestCase;
use BrunoViana\Correios\CalculoPrecoPrazo\Client;
use BrunoViana\Correios\CalculoPrecoPrazo\Client\Request;
use BrunoViana\Correios\CalculoPrecoPrazo\Client\Response;
use BrunoViana\Correios\CalculoPrecoPrazo\Services\CalculadorService;
use BrunoViana\Correios\CalculoPrecoPrazo\Client\Adapters\CurlAdapter;
use BrunoViana\Correios\CalculoPrecoPrazo\Interfaces\Client\HttpRequestInterface;

class CalculadorServiceTest extends TestCase
{
    public function test_Calculador_Deve_Calcular_Passando_Dados_Por_Array_Com_Sucesso()
    {
        $httpMock = $this->createMock(HttpRequestInterface::class);

        $httpMock->method('execute')->willReturn(
            $this->xmlRetornoCorreios()
        );
        
        $httpMock->method('getInfo')
                    ->with(CURLINFO_HTTP_CODE)
                    ->willReturn(200);

        $curlAdapter = new CurlAdapter($httpMock);
        $client = new Client($curlAdapter);

        $calculador = new CalculadorService(
            $client,
            [
                'servicos' => [
                    '41106'
                ],
                'itens' => [
                    [
                        'quantidade' => 1,
                        'peso' => 0.71,
                        'comprimento' => 31,
                        'altura' => 27,
                        'largura' => 31,
                        'diametro' => 0,
                    ]
                ],
                'usuario' => '',
                'senha' => '',
                'origem' => '60842-130',
                'destino' => '22775-051',
                'formato' => 1,
                'mao_propria' => 'N',
                'valor_declarado' => 0,
                'aviso_recebimento' => 'N',
            ]
        );

        $responses = $calculador->calcular();

        $servico = $responses[0];

        $this->assertInstanceOf(Response::class, $servico);
        
        $this->assertEquals('41106', $servico->codigo());
        $this->assertEquals(31, $servico->valor());
        $this->assertEquals(15, $servico->prazoEntrega());
        $this->assertEquals(31, $servico->valorSemAdicionais());
        $this->assertEquals(0, $servico->valorMaoPropria());
        $this->assertEquals(0, $servico->valorAvisoRecebimento());
        $this->assertEquals(0, $servico->valorValorDeclarado());
        $this->assertEquals('S', $servico->entregaDomiciliar());
        $this->assertEquals('N', $servico->entregaSabado());
        $this->assertEquals('', $servico->observacao());
        $this->assertEquals('0', $servico->erro());
        $this->assertEquals('', $servico->mensagemErro());
    }

    private function xmlRetornoCorreios()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <Servicos>
            <cServico>
                <Codigo>41106</Codigo>
                <Valor>31,00</Valor>
                <PrazoEntrega>15</PrazoEntrega>
                <ValorSemAdicionais>31,00</ValorSemAdicionais>
                <ValorMaoPropria>0,00</ValorMaoPropria>
                <ValorAvisoRecebimento>0,00</ValorAvisoRecebimento>
                <ValorValorDeclarado>0,00</ValorValorDeclarado>
                <EntregaDomiciliar>S</EntregaDomiciliar>
                <EntregaSabado>N</EntregaSabado>
                <obsFim></obsFim>
                <Erro>0</Erro>
                <MsgErro></MsgErro>
            </cServico>
        </Servicos>';
    }
}
