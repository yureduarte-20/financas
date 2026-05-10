<?php

namespace Tests\Unit\Service;

use App\Service\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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
 * Como OpenAI\Client é uma classe final (não mockável por Mockery/PHPUnit),
 * os testes unitários focam nos caminhos de validação que não dependem
 * de chamadas HTTP reais. Testes de integração com o provedor OpenAI
 * devem ser executados em ambiente com o serviço disponível.
 */
class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app()->make(AiService::class);
    }

    /**
     * @test
     * O serviço pode ser instanciado corretamente.
     */
    public function it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AiService::class, $this->service);
    }

    /**
     * @test
     * Deve rejeitar tipos de arquivo não suportados.
     */
    public function it_rejects_unsupported_file_types(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não suportado');

        // Act
        $this->service->analyzeExpenseDocument('/tmp/test.exe', 'application/x-msdownload');
    }

    /**
     * @test
     * Deve rejeitar executáveis mesmo com extensão desconhecida.
     */
    public function it_rejects_executable_mime_types(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não suportado');

        // Act
        $this->service->analyzeExpenseDocument('/tmp/test.bin', 'application/octet-stream');
    }

    /**
     * @test
     * Deve rejeitar arquivos de vídeo.
     */
    public function it_rejects_video_files(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não suportado');

        // Act
        $this->service->analyzeExpenseDocument('/tmp/video.mp4', 'video/mp4');
    }

    /**
     * @test
     * Deve rejeitar arquivos de áudio.
     */
    public function it_rejects_audio_files(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não suportado');

        // Act
        $this->service->analyzeExpenseDocument('/tmp/audio.mp3', 'audio/mpeg');
    }

    /**
     * @test
     * Deve rejeitar MIME type vazio ou inválido.
     */
    public function it_rejects_empty_mime_type(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de arquivo não suportado');

        // Act
        $this->service->analyzeExpenseDocument('/tmp/file', '');
    }

    /**
     * @test
     * PDF é um MIME type aceito (não deve lançar exceção de tipo).
     * Nota: Este teste não verifica extração real - apenas a validação de tipo.
     */
    public function it_accepts_pdf_mime_type(): void
    {
        // Não esperamos InvalidArgumentException para PDF
        // Se lançar outra exceção (conexão, PDF inválido), o tipo foi aceito
        try {
            $this->service->analyzeExpenseDocument('/nonexistent/test.pdf', 'application/pdf');
        } catch (InvalidArgumentException $e) {
            // Se falhar com tipo não suportado, o teste falha
            if (str_contains($e->getMessage(), 'Tipo de arquivo não suportado')) {
                $this->fail('PDF deveria ser um tipo suportado, mas foi rejeitado.');
            }
        } catch (\RuntimeException $e) {
            // Runtime exception é esperada - PDF inexistente ou erro de API
            $this->assertStringContainsString('Não foi possível ler o PDF', $e->getMessage());
        } catch (\Exception $e) {
            // Outras exceções são aceitáveis (erro de conexão com provedor, etc.)
            $this->assertTrue(true);
        }

        // Se chegou aqui sem InvalidArgumentException de tipo, passou
        $this->assertTrue(true);
    }
}
