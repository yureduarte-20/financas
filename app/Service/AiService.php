<?php

namespace App\Service;

use Log;
use OpenAI;
use OpenAI\Client;
use Spatie\PdfToText\Pdf;
use Throwable;

class AiService
{
    private Client $client;

    public function __construct()
    {
        $this->client = OpenAI::factory()
            ->withBaseUri(rtrim((string) config('services.openai.base_url'), '/'))
            ->withApiKey((string) config('services.openai.api_key'))
            ->make();
    }

    /**
     * Analisa um PDF ou imagem de recibo/fatura e devolve dados estruturados para revisão.
     *
     * @return array{
     *     estabelecimento: ?string,
     *     data_documento: ?string,
     *     valor_total: ?float,
     *     categoria_sugerida: ?string,
     *     itens: list<array{descricao: string, quantidade: float|int, valor: float, data: ?string}>,
     *     campos_nao_identificados: list<string>
     * }
     */
    public function analyzeExpenseDocument(string $absolutePath, string $mimeType): array
    {
        $mimeType = strtolower($mimeType);

        if ($mimeType === 'application/pdf') {
            $payload = $this->extractPdfPlainText($absolutePath);

            return $this->decodeAnalysisJson(
                $this->requestJsonFromModel($this->buildExpenseExtractionPrompt($payload), null)
            );
        }

        if (str_starts_with($mimeType, 'image/')) {
            $imageBase64 = base64_encode((string) file_get_contents($absolutePath));

            return $this->decodeAnalysisJson(
                $this->requestJsonFromModel(
                    $this->buildExpenseExtractionPrompt(
                        'O documento foi enviado como imagem. Extraia os dados financeiros a partir da imagem.'
                    ),
                    [
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:' . $mimeType . ';base64,' . $imageBase64,
                            ],
                        ],
                    ]
                )
            );
        }

        throw new \InvalidArgumentException('Tipo de arquivo não suportado. Use PDF, JPG ou PNG.');
    }

    private function extractPdfPlainText(string $absolutePath): string
    {
        try {
            $text = Pdf::getText($absolutePath, null, ['layout']);
        } catch (Throwable $e) {
            throw new \RuntimeException('Não foi possível ler o PDF. Verifique se o arquivo não está corrompido ou protegido.', 0, $e);
        }

        $text = preg_replace(
            '/\b(\d{11}|\d{3}\.\d{3}\.\d{3}-\d{2})\b/',
            '***********',
            (string) $text
        ) ?? '';
        $text = preg_replace('/\s*\d{11}\s*/', '***********', $text);
        $max = 60000;
        if (strlen($text) > $max) {
            $text = substr($text, 0, $max) . "\n...[documento truncado para análise]";
        }

        return $text;
    }

    private function buildExpenseExtractionPrompt(string $documentContext): string
    {
        return <<<PROMPT
Analise o documento financeiro (recibo, nota ou fatura) e extraia os dados abaixo.
Responda com UM ÚNICO objeto JSON (sem markdown, sem texto fora do JSON) com esta estrutura exata:
{
  "estabelecimento": string|null,
  "data_documento": string|null (YYYY-MM-DD),
  "valor_total": number|null,
  "categoria_sugerida": string|null (ex.: Alimentação, Transporte, Saúde, Lazer, Moradia, Educação, Outros),
  "itens": [
    {
      "descricao": string,
      "quantidade": number,
      "valor": number,
      "data": string|null (YYYY-MM-DD; se não houver por linha, use a data_documento)
    }
  ],
  "campos_nao_identificados": string[]
}

Regras:
- Se houver várias linhas de itens, inclua cada uma em "itens".
- Se não houver itens detalhados, deixe "itens" como array vazio e preencha valor_total, data_documento e estabelecimento quando possível.
- Use ponto como separador decimal nos números JSON.
- Liste em "campos_nao_identificados" os campos que não conseguiu inferir com segurança (ex.: "valor_total", "data_documento").

Conteúdo / contexto do documento:
{$documentContext}
PROMPT;
    }

    /**
     * @param  list<array<string, mixed>>|null  $imageContentBlocks
     */
    private function requestJsonFromModel(string $textPrompt, ?array $imageContentBlocks): string
    {
        $content = [['type' => 'text', 'text' => $textPrompt]];
        if ($imageContentBlocks !== null) {
            $content = array_merge($content, $imageContentBlocks);
        }

        $params = [
            'model' => config('services.openai.model'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
        ];

        $response = $this->client->chat()->create($params);

        return (string) ($response->choices[0]->message->content ?? '');
    }

    /**
     * @return array{
     *     estabelecimento: ?string,
     *     data_documento: ?string,
     *     valor_total: ?float,
     *     categoria_sugerida: ?string,
     *     itens: list<array{descricao: string, quantidade: float|int, valor: float, data: ?string}>,
     *     campos_nao_identificados: list<string>
     * }
     */
    private function decodeAnalysisJson(string $rawContent): array
    {
        $trimmed = trim($rawContent);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $trimmed, $matches)) {
            $trimmed = $matches[1];
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('A IA não retornou um JSON válido. Tente outro arquivo ou ajuste o modelo nas configurações.');
        }
        Log::info('json response: ' . $trimmed);

        $itens = [];
        if (isset($decoded['itens']) && is_array($decoded['itens'])) {
            foreach ($decoded['itens'] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $itens[] = [
                    'descricao' => isset($item['descricao']) ? (string) $item['descricao'] : 'Item',
                    'quantidade' => isset($item['quantidade']) ? (float) $item['quantidade'] : 1.0,
                    'valor' => isset($item['valor']) ? (float) $item['valor'] : 0.0,
                    'data' => isset($item['data']) && is_string($item['data']) ? $item['data'] : null,
                ];
            }
        }
        Log::info('items response: ' . json_encode($itens));
        $missing = [];
        if (isset($decoded['campos_nao_identificados']) && is_array($decoded['campos_nao_identificados'])) {
            foreach ($decoded['campos_nao_identificados'] as $field) {
                if (is_string($field)) {
                    $missing[] = $field;
                }
            }
        }

        return [
            'estabelecimento' => isset($decoded['estabelecimento']) && is_string($decoded['estabelecimento'])
                ? $decoded['estabelecimento']
                : null,
            'data_documento' => isset($decoded['data_documento']) && is_string($decoded['data_documento'])
                ? $decoded['data_documento']
                : null,
            'valor_total' => isset($decoded['valor_total']) && is_numeric($decoded['valor_total'])
                ? (float) $decoded['valor_total']
                : null,
            'categoria_sugerida' => isset($decoded['categoria_sugerida']) && is_string($decoded['categoria_sugerida'])
                ? $decoded['categoria_sugerida']
                : null,
            'itens' => $itens,
            'campos_nao_identificados' => $missing,
        ];
    }
}
