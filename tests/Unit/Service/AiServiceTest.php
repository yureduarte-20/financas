<?php

namespace Tests\Unit\Service;

use App\Service\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Testes Unitários para AiService
 * 
 * Cobre RF-12 a RF-20: Processamento de documentos com IA
 * 
 * NOTA: Este serviço utiliza um provedor GENÉRICO compatível com OpenAI,
 * conforme configurado no .env.example:
 *   - OPENAI_BASE_URL=http://localhost:1234/v1
 *   - OPENAI_API_KEY=12
 *   - OPENAI_MODEL=openai/gpt-oss-20b
 * 
 * Os testes mockam a API no formato padrão OpenAI (choices/messages).
 * Testes de imagem foram removidos - apenas PDFs são testados.
 */
class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiService();
    }

    protected function tearDown(): void
    {
        Http::assertSentCount(0);
        parent::tearDown();
    }

    /**
     * @test
     * RF-12, RF-13, RF-14: Extrair dados de documento via API compatível com OpenAI.
     */
    public function it_can_extract_data_from_document(): void
    {
        // Arrange
        $expectedData = [
            'estabelecimento' => 'Supermercado ABC',
            'data_documento' => '2026-05-09',
            'valor_total' => 150.50,
            'categoria_sugerida' => 'Alimentação',
            'itens' => [
                ['descricao' => 'Arroz', 'quantidade' => 2, 'valor' => 10.00],
                ['descricao' => 'Feijão', 'quantidade' => 1, 'valor' => 8.50],
            ],
            'campos_nao_identificados' => [],
        ];

        // Mock da API compatível com OpenAI
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($expectedData),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->service->analyzeExpenseDocument('/tmp/test.pdf', 'application/pdf');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('estabelecimento', $result);
        $this->assertArrayHasKey('valor_total', $result);
        $this->assertEquals('Supermercado ABC', $result['estabelecimento']);
        $this->assertEquals(150.50, $result['valor_total']);
        $this->assertArrayHasKey('itens', $result);
        $this->assertCount(2, $result['itens']);
    }

    /**
     * @test
     * RF-19: Tratar erro quando documento não puder ser processado.
     */
    public function it_handles_extraction_errors(): void
    {
        // Arrange
        Http::fake([
            '*' => Http::response(['error' => 'Unable to process'], 500),
        ]);

        // Assert
        $this->expectException(\Exception::class);
        
        // Act
        $this->service->analyzeExpenseDocument('/tmp/test.pdf', 'application/pdf');
    }

    /**
     * @test
     * RF-19: Tratar resposta inválida da API (não-JSON).
     */
    public function it_handles_invalid_json_response(): void
    {
        // Arrange
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'invalid json {',
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Assert
        $this->expectException(\Exception::class);
        
        // Act
        $this->service->analyzeExpenseDocument('/tmp/test.pdf', 'application/pdf');
    }

    /**
     * @test
     * RF-19: Tratar campos não identificados corretamente.
     */
    public function it_handles_missing_fields_gracefully(): void
    {
        // Arrange - Resposta incompleta
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'estabelecimento' => 'Loja X',
                                'valor_total' => null,
                                'data_documento' => null,
                                'categoria_sugerida' => 'Outros',
                                'itens' => [],
                                'campos_nao_identificados' => ['valor_total', 'data_documento'],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->service->analyzeExpenseDocument('/tmp/test.pdf', 'application/pdf');

        // Assert
        $this->assertEquals('Loja X', $result['estabelecimento']);
        $this->assertNull($result['valor_total']);
        $this->assertContains('valor_total', $result['campos_nao_identificados']);
        $this->assertContains('data_documento', $result['campos_nao_identificados']);
    }

    /**
     * @test
     * Deve rejeitar tipos de arquivo não suportados.
     */
    public function it_rejects_unsupported_file_types(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não suportado');
        
        // Act
        $this->service->analyzeExpenseDocument('/tmp/test.exe', 'application/x-msdownload');
    }

    /**
     * @test
     * Deve processar arquivos PDF corretamente.
     */
    public function it_can_process_pdf_files(): void
    {
        // Arrange
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'estabelecimento' => 'Posto Shell',
                                'data_documento' => '2026-01-15',
                                'valor_total' => 250.00,
                                'categoria_sugerida' => 'Transporte',
                                'itens' => [
                                    ['descricao' => 'Gasolina', 'quantidade' => 1, 'valor' => 250.00],
                                ],
                                'campos_nao_identificados' => [],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Act
        $result = $this->service->analyzeExpenseDocument('/tmp/nota.pdf', 'application/pdf');

        // Assert
        $this->assertEquals('Posto Shell', $result['estabelecimento']);
        $this->assertEquals('Transporte', $result['categoria_sugerida']);
    }
}
