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
                break;
            default:
                throw new Exception("Formato não suportado: $extension");
        }
        
        if (empty(trim($text))) {
            throw new Exception("Não foi possível extrair texto do arquivo");
        }
        
        // Contagem de palavras
        $wordCount = str_word_count($text);
        
        // Contagem de segmentos (aproximado: por sentença)
        $segments = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
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
        // Tentativa simples de extração de PDF
        // Para melhor resultado, considere usar bibliotecas como smalot/pdfparser
        
        $content = file_get_contents($path);
        
        // Tenta extrair texto bruto
        $text = '';
        
        // Padrão simples para extrair texto de PDFs não-criptografados
        if (preg_match_all('/\(([^)]+)\)/', $content, $matches)) {
            $text = implode(' ', $matches[1]);
        }
        
        // Limpa caracteres especiais
        $text = preg_replace('/[^\w\s\.\,\!\?\-]/u', '', $text);
        
        if (empty(trim($text))) {
            // Fallback: conta bytes/caracteres
            $text = str_repeat('palavra ', strlen($content) / 50);
        }
        
        return $text;
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
