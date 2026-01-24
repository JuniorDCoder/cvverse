<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class JobCrawlerService
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Crawl a job posting URL and extract information
     *
     * @return array<string, mixed>|null
     *
     * @throws ConnectionException
     */
    public function crawlJobUrl(string $url): ?array
    {
        $maxRetries = 2;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $response = Http::timeout(45) // Increased overall timeout
                    ->connectTimeout(20) // Specific timeout for connection/DNS
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language' => 'en-US,en;q=0.5',
                    ])
                    ->get($url);

                if (! $response->successful()) {
                    Log::error('Failed to fetch job URL', ['url' => $url, 'status' => $response->status()]);

                    return null;
                }

                $html = $response->body();
                $textContent = $this->extractTextContent($html);

                // Use Gemini to analyze the extracted content
                $analysis = $this->geminiService->analyzeJobPosting($textContent);

                if ($analysis) {
                    $analysis['source_url'] = $url;

                    return $analysis;
                }

                return null;
            } catch (ConnectionException $e) {
                $attempt++;
                
                // Check if it's a timeout error
                if (str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'timeout')) {
                    Log::warning('Connection timeout on attempt ' . $attempt, [
                        'url' => $url,
                        'attempt' => $attempt,
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($attempt >= $maxRetries) {
                        Log::error('Max retries reached for job URL crawl', [
                            'url' => $url,
                            'error' => 'Connection timeout - the website may be slow or unreachable'
                        ]);
                        throw new \RuntimeException('Unable to reach the job posting URL. The website may be slow or temporarily unavailable. Please try again later or enter the job details manually.');
                    }
                    
                    // Wait before retry (exponential backoff)
                    sleep(pow(2, $attempt - 1));
                    continue;
                }
                
                // For other connection errors, don't retry
                Log::error('Connection error crawling job URL', ['url' => $url, 'error' => $e->getMessage()]);
                throw new \RuntimeException('Unable to connect to the job posting URL. Please check the URL and try again.');
            } catch (\Exception $e) {
                Log::error('Error crawling job URL', ['url' => $url, 'error' => $e->getMessage()]);

                return null;
            }
        }

        return null;
    }

    /**
     * Extract readable text content from HTML
     */
    private function extractTextContent(string $html): string
    {
        $crawler = new Crawler($html);

        // Remove script and style elements
        $crawler->filter('script, style, nav, footer, header, aside')->each(function (Crawler $node) {
            foreach ($node as $element) {
                $element->parentNode->removeChild($element);
            }
        });

        // Try to find main content areas
        $mainContent = '';

        // Common job posting content selectors
        $selectors = [
            '[class*="job-description"]',
            '[class*="job-content"]',
            '[class*="posting-content"]',
            '[class*="description"]',
            '[id*="job-description"]',
            'article',
            'main',
            '.content',
            '#content',
        ];

        foreach ($selectors as $selector) {
            try {
                $elements = $crawler->filter($selector);
                if ($elements->count() > 0) {
                    $mainContent = $elements->first()->text(' ', true);
                    if (strlen($mainContent) > 200) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback to body if no specific content found
        if (strlen($mainContent) < 200) {
            try {
                $mainContent = $crawler->filter('body')->text(' ', true);
            } catch (\Exception $e) {
                $mainContent = strip_tags($html);
            }
        }

        // Clean up the text
        $mainContent = preg_replace('/\s+/', ' ', $mainContent);
        $mainContent = trim($mainContent);

        // Limit content length for API
        if (strlen($mainContent) > 15000) {
            $mainContent = substr($mainContent, 0, 15000);
        }

        return $mainContent;
    }

    /**
     * Research a company from its website
     *
     * @return array<string, mixed>|null
     *
     * @throws ConnectionException
     */
    public function crawlCompanyWebsite(string $url): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();
            $crawler = new Crawler($html);

            // Extract company name from title or meta
            $companyName = '';
            try {
                $companyName = $crawler->filter('title')->text();
                $companyName = explode('|', $companyName)[0];
                $companyName = explode('-', $companyName)[0];
                $companyName = trim($companyName);
            } catch (\Exception $e) {
                $companyName = parse_url($url, PHP_URL_HOST);
            }

            // Get about page content if available
            $aboutContent = $this->extractTextContent($html);

            // Try to find about page link
            try {
                $aboutLinks = $crawler->filter('a[href*="about"]');
                if ($aboutLinks->count() > 0) {
                    $aboutUrl = $aboutLinks->first()->attr('href');
                    if ($aboutUrl && ! str_starts_with($aboutUrl, 'http')) {
                        $baseUrl = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);
                        $aboutUrl = $baseUrl.'/'.ltrim($aboutUrl, '/');
                    }

                    if ($aboutUrl) {
                        $aboutResponse = Http::timeout(15)->get($aboutUrl);
                        if ($aboutResponse->successful()) {
                            $aboutContent .= ' '.$this->extractTextContent($aboutResponse->body());
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue without about page
            }

            // Use Gemini to research the company
            return $this->geminiService->researchCompany($companyName, $url);
        } catch (\Exception $e) {
            Log::error('Error crawling company website', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Extract company domain from a job URL
     */
    public function extractCompanyDomain(string $jobUrl): ?string
    {
        $host = parse_url($jobUrl, PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        // Handle common job boards - extract company from URL path
        $jobBoards = ['linkedin.com', 'indeed.com', 'glassdoor.com', 'lever.co', 'greenhouse.io', 'workable.com'];

        foreach ($jobBoards as $board) {
            if (str_contains($host, $board)) {
                // For job boards, we can't easily extract company domain
                return null;
            }
        }

        return 'https://'.$host;
    }
}
