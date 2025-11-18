<?php
// /app/Nov_16/processor.php
// Processador de documentos com estimativa de páginas

class DocumentProcessor
{
    /**
     * Processa um arquivo e retorna análise de fuzzy matches
     * 
     * @param string $tmpPath Caminho temporário do arquivo
     * @param string $fileName Nome original do arquivo
     * @return array Resultado com wordCount, segmentCount, fuzzy, estimatedPages
     */
    public function process($tmpPath, $fileName)
    {
        // Garante que o nome do arquivo está em UTF-8
        if (!mb_check_encoding($fileName, 'UTF-8')) {
            $fileName = mb_convert_encoding($fileName, 'UTF-8', 'auto');
        }
        
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $text = '';
        
        switch ($extension) {
            case 'docx':
                $text = $this->extractFromDocx($tmpPath);
                break;
            case 'pptx':
                $text = $this->extractFromPptx($tmpPath);
                break;
            case 'xlsx':
            case 'xls':
                $text = $this->extractFromXlsx($tmpPath);
                break;
            case 'txt':
                $text = file_get_contents($tmpPath);
                // Garante UTF-8 para arquivos de texto
                if (!mb_check_encoding($text, 'UTF-8')) {
                    $text = mb_convert_encoding($text, 'UTF-8', 'auto');
                }
                break;
            case 'pdf':
                $text = $this->extractFromPdf($tmpPath);
                break;
            case 'html':
            case 'htm':
                $text = $this->extractFromHtml($tmpPath);
                break;
            case 'csv':
                $text = $this->extractFromCsv($tmpPath);
                break;
            case 'md':
                $text = file_get_contents($tmpPath);
                // Garante UTF-8 para arquivos markdown
                if (!mb_check_encoding($text, 'UTF-8')) {
                    $text = mb_convert_encoding($text, 'UTF-8', 'auto');
                }
                break;
            default:
                throw new Exception("Formato não suportado: $extension");
        }
        
        if (empty(trim($text))) {
            throw new Exception("Não foi possível extrair texto do arquivo");
        }
        
        // Limpa e normaliza o texto para contagem precisa
        $text = $this->normalizeTextForCounting($text);
        
        // Contagem de palavras com suporte UTF-8
        // Usa mb_split para melhor precisão com acentos
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
        
        // Contagem de segmentos (aproximado: por sentença)
        $segments = preg_split('/[.!?]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $segmentCount = count($segments);
        
        // Gera fuzzy matches simulados (distribuição aleatória mas realista)
        $fuzzyData = $this->generateFuzzyMatches($segmentCount);
        
        // NOVO: Estima páginas (250 palavras = 1 página)
        $estimatedPages = max(1, (int)round($wordCount / 250));
        
        return [
            'fileName' => $fileName,
            'wordCount' => $wordCount,
            'segmentCount' => $segmentCount,
            'fuzzy' => $fuzzyData,
            'estimatedPages' => $estimatedPages,
        ];
    }
    
    /**
     * Normaliza texto para contagem precisa de palavras
     * Remove quebras de linha desnecessárias e espaços duplicados
     */
    private function normalizeTextForCounting($text)
    {
        // Remove quebras de linha no meio de frases (hifenização)
        // Ex: "pala-\nvras" vira "palavras"
        $text = preg_replace('/(\w)-\s*\n\s*(\w)/u', '$1$2', $text);
        
        // Substitui quebras de linha por espaços (exceto múltiplas quebras que indicam parágrafos)
        $text = preg_replace('/(?<!\n)\n(?!\n)/u', ' ', $text);
        
        // Remove múltiplos espaços
        $text = preg_replace('/\s+/u', ' ', $text);
        
        // Remove espaços antes de pontuação
        $text = preg_replace('/\s+([.,;:!?])/u', '$1', $text);
        
        // Remove caracteres de controle e espaços não-quebráveis problemáticos
        $text = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $text);
        
        // Normaliza espaços novamente após limpeza
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Gera dados de fuzzy matches simulados
     */
    private function generateFuzzyMatches($totalSegments)
    {
        // Distribuição típica de fuzzy matches em projetos de tradução
        $distribution = [
            '100%' => 0.05,      // 5% matches perfeitos
            '95-99%' => 0.10,    // 10% quase perfeitos
            '85-94%' => 0.15,    // 15% alta similaridade
            '75-84%' => 0.20,    // 20% média-alta
            '50-74%' => 0.25,    // 25% média
            'No Match' => 0.25,  // 25% sem correspondência
        ];
        
        $result = [];
        $remaining = $totalSegments;
        
        foreach ($distribution as $category => $percentage) {
            if ($category === 'No Match') {
                $segments = $remaining; // Resto vai para No Match
            } else {
                $segments = (int)round($totalSegments * $percentage);
                $remaining -= $segments;
            }
            
            $result[] = [
                'category' => $category,
                'segments' => $segments,
                'percentage' => ($segments / $totalSegments) * 100,
            ];
        }
        
        return $result;
    }
    
    /**
     * Extrai texto de arquivo DOCX
     */
    private function extractFromDocx($path)
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("Extensão ZipArchive não está disponível");
        }
        
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception("Não foi possível abrir o arquivo DOCX");
        }
        
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        if ($xml === false) {
            throw new Exception("Não foi possível extrair conteúdo do DOCX");
        }
        
        // Remove tags XML e retorna texto
        $text = strip_tags($xml);
        return $text;
    }
    
    /**
     * Extrai texto de arquivo PPTX
     */
    private function extractFromPptx($path)
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("Extensão ZipArchive não está disponível");
        }
        
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception("Não foi possível abrir o arquivo PPTX");
        }
        
        $text = '';
        
        // Percorre slides
        for ($i = 1; $i <= 100; $i++) {
            $slideXml = $zip->getFromName("ppt/slides/slide{$i}.xml");
            if ($slideXml === false) break;
            
            $text .= strip_tags($slideXml) . "\n";
        }
        
        $zip->close();
        
        return $text;
    }
    
    /**
     * Extrai texto de arquivo XLSX
     */
    private function extractFromXlsx($path)
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("Extensão ZipArchive não está disponível");
        }
        
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception("Não foi possível abrir o arquivo XLSX");
        }
        
        $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();
        
        if ($sharedStrings === false) {
            throw new Exception("Não foi possível extrair conteúdo do XLSX");
        }
        
        $text = strip_tags($sharedStrings);
        return $text;
    }
    
    /**
     * Extrai texto de arquivo PDF
     */
    private function extractFromPdf($path)
    {
        // Tenta usar Smalot PdfParser se disponível
        if (class_exists('Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
                
                // Remove caracteres de controle mas preserva UTF-8
                $text = preg_replace('/[ --]/u', ' ', $text);
                
                // Remove hifenização no final de linha
                $text = preg_replace('/(\w)-\s*\n\s*(\w)/u', '$1$2', $text);
                
                if (!empty(trim($text))) {
                    return $text;
                }
            } catch (Exception $e) {
                // Continua para método alternativo
            }
        }
        
        // Método alternativo: extração básica
        $content = file_get_contents($path);
        $text = '';
        
        // Extrai texto entre parênteses com suporte UTF-8
        if (preg_match_all('/\((.*?)\)/s', $content, $matches)) {
            foreach ($matches[1] as $match) {
                // Decodifica sequências de escape UTF-16BE comum em PDFs
                $decoded = $this->decodePdfString($match);
                $text .= ' ' . $decoded;
            }
        }
        
        // Extrai também texto entre colchetes angulares (hexadecimal)
        if (preg_match_all('/<([0-9A-Fa-f]+)>/', $content, $hexMatches)) {
            foreach ($hexMatches[1] as $hex) {
                $decoded = '';
                for ($i = 0; $i < strlen($hex); $i += 4) {
                    $code = hexdec(substr($hex, $i, 4));
                    if ($code > 0) {
                        $decoded .= mb_chr($code, 'UTF-8');
                    }
                }
                $text .= ' ' . $decoded;
            }
        }
        
        // Limpa o texto preservando UTF-8
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);
        
        if (empty($text)) {
            // Fallback: estimativa baseada no tamanho do arquivo
            $fileSize = filesize($path);
            $estimatedWords = max(100, intval($fileSize / 20));
            $text = str_repeat('palavra ', $estimatedWords);
        }
        
        return $text;
    }
    
    /**
     * Decodifica string PDF com suporte a UTF-8 e UTF-16BE
     */
    private function decodePdfString($string)
    {
        // Remove escape de parênteses
        $string = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $string);
        
        // Detecta UTF-16BE (começa com \xFE\xFF)
        if (substr($string, 0, 2) === "\xFE\xFF") {
            $string = mb_convert_encoding(substr($string, 2), 'UTF-8', 'UTF-16BE');
        }
        // Tenta converter de ISO-8859-1 para UTF-8 se necessário
        elseif (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        
        return $string;
    }
    
    /**
     * Extrai texto de arquivo HTML
     */
    private function extractFromHtml($path)
    {
        $html = file_get_contents($path);
        
        // Remove scripts e styles
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        
        // Remove tags HTML
        $text = strip_tags($html);
        
        // Decodifica entidades HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $text;
    }
    
    /**
     * Extrai texto de arquivo CSV
     */
    private function extractFromCsv($path)
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new Exception("Não foi possível abrir o arquivo CSV");
        }
        
        $text = '';
        
        while (($row = fgetcsv($handle)) !== false) {
            $text .= implode(' ', $row) . "\n";
        }
        
        fclose($handle);
        
        return $text;
    }
}
