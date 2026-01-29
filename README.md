# AI SEO Readiness Auditor

A high-performance WordPress plugin that mimics AI crawlers to evaluate your website's visibility to Large Language Models (LLMs) and search engines. It provides a weighted "Crawler Visibility" score and actionable, evidence-based reporting.

## Changelog

### v2.1.0 (Scoring Accuracy Update)
*   **Refactor:** Converted "Readability" and "Image Alt Text" to a precise percentage-based scoring system (0-100).
*   **Feature:** Added complex sentence detection (>25 words) with list-based evidence reporting.
*   **Enhancement:** Improved "Fix-it List" UI with category labels and detailed dropdowns.
*   **Enhancement:** Refined decorative image filtering (ignoring social icons, smart links, and decorative filenames).

### v2.0.0 (Major Overhaul)
*   **New Scoring Matrix:** Transitioned to a 9-category weighted model (v2.0).
*   **Evidence-Based Reporting:** Enhanced DOM crawler to extract exact HTML snippets for issues.
*   **JS Detection:** Added heuristic detection for Client-Side Rendering (CSR).

## Features

-   **Crawler Visibility Score:** A 0-100 weighted score based on 9 key metrics including Content Clarity, Page Structure, and AI Clarity.
-   **JavaScript Dependency Detection:** Heuristically detects if your site relies on Client-Side Rendering (CSR), which can hide content from AI bots.
-   **Evidence-Based Reporting:** Highlights specific issues (e.g., "Missing Alt Text on 3 images") with code snippets and actionable fixes.
-   **Secure & Performant:** Uses WordPress nonces for security and optimizes DOM parsing for speed.

## Scoring Matrix

The audit evaluates your page across 9 categories:

1.  **Content Clarity (20%)**: Text density, word count, and code-to-text ratio.
2.  **Page Structure (15%)**: H1/H2 hierarchy and logical flow.
3.  **Title (10%)**: Length and descriptiveness.
4.  **Readability (10%)**: Sentence and paragraph length.
5.  **Image Alt Text (10%)**: Accessibility and AI vision support.
6.  **AI Clarity (10%)**: Core identity signals (e.g., "About Us", "Contact").
7.  **Schema / Structured Data (10%)**: Presence of JSON-LD.
8.  **Technical SEO (10%)**: Viewport settings and crawlability.
9.  **Metadata (5%)**: Meta description quality.

## Installation

1.  Download the latest `ai-seo-readiness.zip` from the [Releases](https://github.com/cjayacopra/ai-seo-readiness/releases) page.
2.  In your WordPress dashboard, go to **Plugins > Add New > Upload Plugin**.
3.  Select the downloaded `.zip` file and click **Install Now**.
4.  Activate the plugin.
5.  Use the shortcode `[ai-seo-readiness]` on any page to display the audit tool, or access it via the admin menu.

## Usage

1.  Navigate to the "AI SEO Readiness" page in your WordPress dashboard or the page where you placed the shortcode.
2.  Enter the URL of the website you want to audit.
3.  Click "Crawl Website" to generate the report.
4.  Review the score, specific metrics, and the "Recommended Fix-it List" for actionable improvements.

## Technical Architecture

For developers and curious users, here is how the plugin processes a request:

1.  **Crawling (`site-ai-auditor.php`)**:
    The plugin acts as a proxy, fetching the target URL using `wp_remote_get` with a Chrome User-Agent header. It parses the raw HTML using PHP's `DOMDocument` to extract text, tags, and attributes.

2.  **Scoring (`includes/scoring-rules.php`)**:
    The extracted data is passed through 9 validators. Each validator returns a score and a list of "evidence" (e.g., specific sentences that are too long).

3.  **Weighting (`includes/class-auditor-scan.php`)**:
    The raw scores are weighted according to the **Crawler Visibility Matrix** (e.g., Content Clarity counts for 20% of the total).

4.  **Rendering (`includes/class-auditor-results.php`)**:
    The final score and evidence list are injected into an HTML template and returned to your browser via AJAX.

## Requirements

-   WordPress 5.0 or higher
-   PHP 7.4 or higher
-   `DOMDocument` PHP extension enabled
