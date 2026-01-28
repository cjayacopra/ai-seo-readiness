# AI SEO Readiness Auditor

A high-performance WordPress plugin that mimics AI crawlers to evaluate your website's visibility to Large Language Models (LLMs) and search engines. It provides a weighted "Crawler Visibility" score and actionable, evidence-based reporting.

## Features

-   **Crawler Visibility Score:** A 0-100 weighted score based on 9 key metrics including Content Clarity, Page Structure, and AI Clarity.
-   **JavaScript Dependency Detection:** Heuristically detects if your site relies on Client-Side Rendering (CSR), which can hide content from AI bots.
-   **Evidence-Based Reporting:** Highlights specific issues (e.g., "Missing Alt Text on 3 images") with code snippets and actionable fixes.
-   **Secure & Performant:** Uses WordPress nonces for security and optimizes DOM parsing for speed.

## Scoring Matrix (v2.0)

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

1.  Upload the `ai-seo-readiness` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Use the shortcode `[ai-seo-readiness]` on any page to display the audit tool, or access it via the admin menu.

## Usage

1.  Navigate to the "AI SEO Readiness" page in your WordPress dashboard or the page where you placed the shortcode.
2.  Enter the URL of the website you want to audit.
3.  Click "Crawl Website" to generate the report.
4.  Review the score, specific metrics, and the "Recommended Fix-it List" for actionable improvements.

## Requirements

-   WordPress 5.0 or higher
-   PHP 7.4 or higher
-   `DOMDocument` PHP extension enabled
