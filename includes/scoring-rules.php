<?php
/**
 * JJSC IT SOLUTIONS — AI READABILITY & SEO SCORING RULES
 * Evidence-Based with Snippet Details
 */

if (!defined('ABSPATH'))
    exit;

class Site_Auditor_Scoring_Rules
{

    protected $r;

    public function __construct(array $reports)
    {
        $this->r = $reports;
    }

    /**
     * 1. Title (10%)
     */
    public function scoreTitle()
    {
        $score = 0;
        $evidence = [];
        $title = trim($this->r['title'] ?? '');

        if (empty($title) || $title === 'N/A') {
            $score = 0;
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Missing Page Title',
                'summary' => 'No <title> tag found in the page header.',
                'fix' => 'Add a descriptive title tag to your website header.'
            ];
        } else {
            $len = strlen($title);
            if ($len >= 10 && $len <= 60) {
                $score = 5;
            } elseif ($len < 10) {
                $score = 1;
                $evidence[] = [
                    'severity' => 'warning',
                    'message' => 'Title Too Short',
                    'summary' => 'Your title is only ' . $len . ' characters.',
                    'details' => [esc_html($title)],
                    'fix' => 'Expand your title to 50-60 characters to improve visibility.'
                ];
            } else {
                $score = 3;
                $evidence[] = [
                    'severity' => 'warning',
                    'message' => 'Title Too Long',
                    'summary' => 'Your title is ' . $len . ' characters.',
                    'details' => [esc_html($title)],
                    'fix' => 'Shorten your title to under 60 characters to prevent cutting off in search results.'
                ];
            }
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 2. Metadata (5%)
     */
    public function scoreMetadata()
    {
        $score = 0;
        $evidence = [];
        $meta = trim($this->r['meta_description'] ?? '');

        if (empty($meta)) {
            $score = 0;
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Missing Meta Description',
                'summary' => 'No description found in the page metadata.',
                'fix' => 'Add a unique meta description to summarize your page for AI and users.'
            ];
        } else {
            $len = strlen($meta);
            if ($len >= 120 && $len <= 160) {
                $score = 5;
            } elseif ($len < 50) {
                $score = 2;
                $evidence[] = [
                    'severity' => 'warning',
                    'message' => 'Meta Description Too Short',
                    'summary' => 'Description is only ' . $len . ' characters.',
                    'details' => [esc_html($meta)],
                    'fix' => 'Expand your meta description to approx. 150 characters.'
                ];
            } else {
                $score = 4;
            }
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 3. Page Structure (15%)
     */
    public function scorePageStructure()
    {
        $score = 0;
        $evidence = [];
        $h1s = $this->r['evidence_raw']['h1_tags'] ?? [];
        $h1Count = count($h1s);
        $subHeadings = count($this->r['headings']) - $h1Count;

        if ($h1Count === 1) {
            $score = 5;
        } elseif ($h1Count === 0) {
            $score = 0;
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Missing H1 Heading',
                'summary' => 'No H1 tag found. AI uses this to understand the main topic.',
                'fix' => 'Add exactly one <h1> tag to your page content.'
            ];
        } else {
            $score = 2;
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Multiple H1 Headings',
                'summary' => 'Found ' . $h1Count . ' H1 tags. This can confuse crawlers.',
                'details' => array_map('esc_html', $h1s),
                'fix' => 'Consolidate your content to use only one main H1 tag.'
            ];
        }

        if ($subHeadings < 2) {
            $score = max(0, $score - 2);
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Weak Heading Hierarchy',
                'summary' => 'Very few subheadings (H2, H3) found.',
                'fix' => 'Use subheadings to organize your content into logical sections.'
            ];
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 4. Content Clarity (20%)
     */
    public function scoreContentClarity()
    {
        $score = 3;
        $evidence = [];
        $words = $this->r['text_length'];

        if ($words < 100) {
            $score = 0;
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Extremely Thin Content',
                'summary' => 'Only ' . $words . ' words detected.',
                'fix' => 'Add more descriptive text about your business and services.'
            ];
        } elseif ($words > 300) {
            $score = 5;
        }

        if ($this->r['text_to_code_ratio'] < 5) {
            $score = max(0, $score - 2);
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Low Text Density',
                'summary' => 'The page is mostly code with very little visible text.',
                'fix' => 'Ensure your main content is not hidden inside complex scripts or styles.'
            ];
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 5. Readability (10%)
     */
    public function scoreReadability()
    {
        // New Logic: Direct Percentage of Accessible Sentences
        $total = $this->r['sentence_count'] ?? 0;
        $complex = $this->r['complex_sentence_count'] ?? 0;
        $evidence = [];

        if ($total === 0) {
            return ['score' => 100, 'evidence' => []];
        }

        $accessible = max(0, $total - $complex);
        // Formula: (Accessible / Total) * 100
        $score = ($accessible / $total) * 100;
        $score = round($score);

        if ($complex > 0) {
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Complex Sentences Detected',
                'summary' => $complex . ' out of ' . $total . ' sentences are long (>25 words).',
                'details' => $this->r['complex_sentences_list'] ?? [],
                'fix' => 'Break long sentences into two to make them easier for AI to parse.'
            ];
        }

        // Check for Dense Paragraphs (Secondary Penalty, max 10% deduction)
        $avgPara = $this->r['avg_para_len'];
        if ($avgPara > 150) {
            $score = max(0, $score - 10);
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Dense Paragraphs',
                'summary' => 'Paragraphs are too long and hard to scan.',
                'fix' => 'Use more frequent line breaks to create "white space".'
            ];
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 6. Image Alt Text (10%)
     */
    public function scoreImageAlt()
    {
        // New Logic: Direct Percentage of Meaningful Images (0-100)
        $evidence = [];
        $missing = $this->r['evidence_raw']['missing_alt'] ?? [];
        $totalMeaningful = count($this->r['images']);

        if ($totalMeaningful === 0) {
            // Neutral/Good if no images to check
            return [
                'score' => 100,
                'evidence' => [
                    [
                        'severity' => 'warning',
                        'message' => 'No Images Detected',
                        'summary' => 'Your page has no meaningful images to support your content.',
                        'fix' => 'Add relevant images to make your site more engaging.'
                    ]
                ]
            ];
        }

        $missingCount = count($missing);
        $goodCount = $totalMeaningful - $missingCount;

        // Formula: (Alt Count / Total Meaningful) * 100
        // Ensure we don't divide by zero (handled above) 
        // and cap at 100 (though math shouldn't exceed it)
        $score = ($goodCount / $totalMeaningful) * 100;
        $score = round($score);

        if ($missingCount > 0) {
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Missing Image Alt Text',
                // Contextualize: X of Y images are missing alts
                'summary' => $missingCount . ' out of ' . $totalMeaningful . ' meaningful images are missing descriptions.',
                'details' => array_column($missing, 'tag'),
                'fix' => 'Add "alt" attributes to these images so AI can "see" them.'
            ];
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 7. AI Clarity (10%)
     */
    public function scoreAIClarity()
    {
        $score = 0;
        $evidence = [];
        $signals = $this->r['identity_signals'] ?? [];

        if (count($signals) >= 3) {
            $score = 5;
        } elseif (count($signals) > 0) {
            $score = 3;
        } else {
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Weak Identity Signals',
                'summary' => 'AI may struggle to identify your core business type.',
                'fix' => 'Use standard terms like "Services", "About Us", and "Contact" in your headers.'
            ];
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 8. Schema / Structured Data (10%)
     */
    public function scoreSchema()
    {
        $score = 0;
        $evidence = [];

        if (!empty($this->r['schema'])) {
            $score = 5;
        } else {
            $evidence[] = [
                'severity' => 'warning',
                'message' => 'Missing Schema Markup',
                'summary' => 'No structured data (JSON-LD) detected.',
                'fix' => 'Add Organization or LocalBusiness schema to help AI verify your details.'
            ];
        }

        return ['score' => $score, 'evidence' => $evidence];
    }

    /**
     * 9. Technical SEO (10%)
     */
    public function scoreTechnicalSEO()
    {
        $score = 5;
        $evidence = [];

        if (empty($this->r['viewport'])) {
            $score -= 3;
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Mobile Optimization Issue',
                'summary' => 'Missing viewport meta tag.',
                'fix' => 'Add a viewport meta tag to ensure your site works on mobile devices.'
            ];
        }

        if (stripos($this->r['meta_robots'], 'noindex') !== false) {
            $score = 0;
            $evidence[] = [
                'severity' => 'error',
                'message' => 'Search Engine Blocked',
                'summary' => 'A "noindex" tag is preventing AI from reading this page.',
                'fix' => 'Remove the "noindex" instruction from your site settings.'
            ];
        }

        return ['score' => max(0, $score), 'evidence' => $evidence];
    }
}