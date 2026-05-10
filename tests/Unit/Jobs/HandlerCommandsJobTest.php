<?php

namespace Tests\Unit\Jobs;

use App\Jobs\HandlerCommandsJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes Unitários para HandlerCommandsJob
 * 
 * Cobre RF-31 a RF-45: Integração com Telegram
 * 
 * NOTA: O HandlerCommandsJob não recebe parâmetros no construtor.
 * O job obtém os dados do webhook via Telegram::commandsHandler(true)
 * no método handle(). Os testes unitários verificam a instanciação
 * e propriedades da classe. Testes de integração com Telegram
 * requerem um ambiente com bot token configurado.
 */
class HandlerCommandsJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * RF-31, RF-32: O job pode ser instanciado sem parâmetros.
     */
    public function it_can_be_instantiated(): void
    {
        // Act
        $job = new HandlerCommandsJob();

        // Assert
        $this->assertInstanceOf(HandlerCommandsJob::class, $job);
    }

    /**
     * @test
     * Job implementa ShouldQueue.
     */
    public function it_implements_should_queue(): void
    {
        // Arrange
        $job = new HandlerCommandsJob();

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $job);
    }

    /**
     * @test
     * Job usa trait Queueable.
     */
    public function it_uses_queueable_trait(): void
    {
        // Arrange
        $job = new HandlerCommandsJob();

        // Assert
        $this->assertContains(
            \Illuminate\Foundation\Queue\Queueable::class,
            class_uses($job)
        );
    }

    /**
     * @test
     * Job pode ser serializado.
     */
    public function it_can_be_serialized(): void
    {
        // Arrange
        $job = new HandlerCommandsJob();

        // Act
        $serialized = serialize($job);
        $unserialized = unserialize($serialized);

        // Assert
        $this->assertInstanceOf(HandlerCommandsJob::class, $unserialized);
    }

    /**
     * @test
     * Várias instâncias são independentes.
     */
    public function it_creates_independent_instances(): void
    {
        // Act
        $job1 = new HandlerCommandsJob();
        $job2 = new HandlerCommandsJob();

        // Assert
        $this->assertNotSame($job1, $job2);
        $this->assertInstanceOf(HandlerCommandsJob::class, $job1);
        $this->assertInstanceOf(HandlerCommandsJob::class, $job2);
    }
}
