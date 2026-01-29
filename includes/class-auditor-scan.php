<?php
if (!defined('ABSPATH'))
    exit;

require_once __DIR__ . '/scoring-rules.php';

class Site_Auditor_Scan
{

    protected $scores = [];
    protected $raw_scores = [];
    protected $evidence = [];
    protected $js_analysis = [];
    protected $rules;
    protected $total_score = 0;

    public function __construct(array $reports)
    {
        $this->rules = new Site_Auditor_Scoring_Rules($reports);
        $this->js_analysis = $reports['js_detection'] ?? [];
        $this->run();
    }

    protected function run()
    {
        // 1. Define Weights
        $weights = [
            'title' => 0.10,
            'metadata' => 0.05,
            'page_structure' => 0.15,
            'content_clarity' => 0.20,
            'readability' => 0.10,
            'image_alt' => 0.10,
            'ai_clarity' => 0.10,
            'schema' => 0.10,
            'tech_seo' => 0.10,
        ];

        // 2. Run Rules
        $results = [
            'title' => $this->rules->scoreTitle(),
            'metadata' => $this->rules->scoreMetadata(),
            'page_structure' => $this->rules->scorePageStructure(),
            'content_clarity' => $this->rules->scoreContentClarity(),
            'readability' => $this->rules->scoreReadability(),
            'image_alt' => $this->rules->scoreImageAlt(),
            'ai_clarity' => $this->rules->scoreAIClarity(),
            'schema' => $this->rules->scoreSchema(),
            'tech_seo' => $this->rules->scoreTechnicalSEO(),
        ];

        // 3. Calculate Weighted Score
        foreach ($results as $cat => $data) {
            $raw = $data['score']; // 0-5 OR 0-100 (for image_alt, readability)
            $this->raw_scores[$cat] = $raw;

            if ($cat === 'image_alt' || $cat === 'readability') {
                // Direct Percentage (0-100)
                // Contribution = Percentage * Weight
                $weighted = $raw * $weights[$cat];
            } else {
                // Standard 0-5 Scale
                // Normalize to 100-point scale contribution
                // (Raw / 5) * 100 * Weight
                $weighted = ($raw / 5) * 100 * $weights[$cat];
            }

            $this->total_score += $weighted;

            // Collect Evidence with Category Label
            if (!empty($data['evidence'])) {
                $categoryLabels = [
                    'title' => 'Page Title',
                    'metadata' => 'Metadata',
                    'page_structure' => 'Page Structure',
                    'content_clarity' => 'Content Clarity',
                    'readability' => 'Readability',
                    'image_alt' => 'Image Alt Text',
                    'ai_clarity' => 'AI Clarity',
                    'schema' => 'Schema Data',
                    'tech_seo' => 'Technical SEO'
                ];

                foreach ($data['evidence'] as $item) {
                    $item['category'] = $categoryLabels[$cat] ?? ucfirst(str_replace('_', ' ', $cat));
                    $this->evidence[] = $item;
                }
            }
        }

        // Ensure integer
        $this->total_score = round($this->total_score);
    }

    public function getScores()
    {
        // Return raw scores (0-5) for the UI bars
        return $this->raw_scores;
    }

    public function getEvidence()
    {
        return $this->evidence;
    }

    public function getJSAnalysis()
    {
        return $this->js_analysis;
    }

    public function getTotal()
    {
        return $this->total_score;
    }

    public function getRemarks()
    {
        $total = $this->getTotal();

        if ($total >= 86)
            return '🌟 AI-ready & future-proof';
        if ($total >= 70)
            return '✅ Strong foundation';
        if ($total >= 40)
            return '⚠️ Partially visible, clarity gaps';
        return '🚨 High risk: machines struggle to understand this page';
    }
}